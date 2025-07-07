<?php

namespace App\Filament\Resources\GuardianResource\Actions;

use App\Models\Student;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms;
use Illuminate\Database\Eloquent\Collection;

class BulkAssignStudentsAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'bulk_assign_students';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('ربط طلاب بالجملة')
            ->modalHeading('ربط عدة طلاب بأولياء الأمور المحددين')
            ->modalDescription('سيتم ربط الطلاب المحددين بجميع أولياء الأمور المختارين')
            ->icon('heroicon-o-link')
            ->color('success')
            ->form([
                Forms\Components\Select::make('students')
                    ->label('اختر الطلاب')
                    ->multiple()
                    ->searchable(['name_ar', 'name_en', 'code', 'student_number'])
                    ->getSearchResultsUsing(function (string $search) {
                        return Student::where('name_ar', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%")
                            ->active()
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(function ($student) {
                                return [$student->id => "{$student->name_ar} - كود: {$student->code}"];
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $student = Student::find($value);
                        return $student ? "{$student->name_ar} - كود: {$student->code}" : null;
                    })
                    ->required()
                    ->helperText('ابحث عن الطلاب واختر المطلوب ربطهم'),

                Forms\Components\Toggle::make('is_primary')
                    ->label('تعيين كولي أمر رئيسي')
                    ->default(false)
                    ->helperText('هل تريد تعيين هؤلاء الأولياء كأولياء أمر رئيسيين للطلاب المحددين؟'),

                Forms\Components\Toggle::make('replace_existing')
                    ->label('استبدال الروابط الموجودة')
                    ->default(false)
                    ->helperText('هل تريد إزالة الروابط الموجودة مسبقاً وإنشاء روابط جديدة؟'),
            ])
            ->action(function (Collection $records, array $data): void {
                $studentIds = $data['students'];
                $isPrimary = $data['is_primary'];
                $replaceExisting = $data['replace_existing'];

                foreach ($records as $guardian) {
                    foreach ($studentIds as $studentId) {
                        if ($replaceExisting) {
                            // إزالة الروابط الموجودة إذا كان مطلوباً
                            $guardian->students()->detach($studentId);
                        }

                        // إضافة الرابط الجديد
                        $guardian->students()->syncWithoutDetaching([
                            $studentId => ['is_primary' => $isPrimary]
                        ]);
                    }
                }

                // إشعار بالنجاح
                $guardianCount = $records->count();
                $studentCount = count($studentIds);
                
                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('تم ربط الطلاب بنجاح')
                    ->body("تم ربط {$studentCount} طلاب مع {$guardianCount} ولي أمر")
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}