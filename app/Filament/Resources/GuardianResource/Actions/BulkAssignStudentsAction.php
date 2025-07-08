<?php

namespace App\Filament\Resources\GuardianResource\Actions;

use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BulkAssignStudentsAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'bulk_assign_students';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('ربط الطلاب')
            ->icon('heroicon-o-academic-cap')
            ->color('primary')
            ->form([
                Repeater::make('students')
                    ->label('الطلاب')
                    ->schema([
                        Select::make('student_id')
                            ->label('الطالب')
                            ->searchable()
                            ->options(function () {
                                return Student::query()
                                    ->where('school_id', auth()->user()->school_id)
                                    ->where('is_active', true)
                                    ->pluck('name_ar', 'id');
                            })
                            ->required(),
                        Toggle::make('is_primary')
                            ->label('ولي أمر رئيسي')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->addActionLabel('إضافة طالب')
                    ->minItems(1)
                    ->maxItems(10)
                    ->reorderable(false)
                    ->collapsible()
                    ->cloneable()
            ])
            ->action(function (array $data, Collection $records): void {
                if (empty($data['students'])) {
                    Notification::make()
                        ->title('خطأ')
                        ->body('يجب اختيار طالب واحد على الأقل')
                        ->danger()
                        ->send();
                    return;
                }

                DB::transaction(function () use ($data, $records) {
                    foreach ($records as $guardian) {
                        foreach ($data['students'] as $studentData) {
                            $studentId = $studentData['student_id'];
                            $isPrimary = $studentData['is_primary'] ?? false;

                            // التحقق من وجود الطالب والتأكد من أنه في نفس المدرسة
                            $student = Student::where('id', $studentId)
                                ->where('school_id', $guardian->school_id)
                                ->first();
                            
                            if (!$student) {
                                continue;
                            }

                            // إزالة الرابط الموجود إذا كان موجوداً
                            if ($guardian->students()->where('student_id', $studentId)->exists()) {
                                $guardian->students()->detach($studentId);
                            }

                            // إضافة الرابط الجديد إلى الجدول الوسيط
                            $guardian->students()->attach($studentId, [
                                'is_primary' => $isPrimary,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                });

                // إشعار بالنجاح
                $guardianCount = $records->count();
                $studentCount = count($data['students']);
                
                Notification::make()
                    ->title('تم بنجاح')
                    ->body("تم ربط {$studentCount} طالب بـ {$guardianCount} ولي أمر")
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
