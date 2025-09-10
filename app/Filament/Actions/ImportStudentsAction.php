<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Student;

class ImportStudentsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'import_students';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('استيراد الطلاب')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->form([
                Section::make('استيراد البيانات من Excel')
                    ->description('قم برفع ملف Excel يحتوي على بيانات الطلاب')
                    ->schema([
                        FileUpload::make('excel_file')
                            ->label('ملف Excel')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->maxSize(10240) // 10MB
                            ->directory('imports')
                            ->visibility('public')
                            ->downloadable(false),

                        Select::make('default_branch_id')
                            ->label('الفرع الافتراضي')
                            ->options(function () {
                                return Branch::where('school_id', auth()->user()?->school_id)
                                    ->where('is_active', true)
                                    ->pluck('name_ar', 'id');
                            })
                            ->placeholder('اختر الفرع الافتراضي')
                            ->helperText('سيتم استخدام هذا الفرع للطلاب الذين لا يحتوون على فرع محدد')
                            ->searchable()
                            ->preload(),

                        Select::make('import_mode')
                            ->label('وضع الاستيراد')
                            ->options([
                                'create_only' => 'إنشاء جديد فقط',
                                'update_existing' => 'تحديث الموجود أو إنشاء جديد',
                            ])
                            ->default('create_only')
                            ->required()
                            ->helperText('اختر إنشاء جديد فقط لتجنب تحديث البيانات الموجودة'),

                        Placeholder::make('required_fields_info')
                            ->label('الحقول المطلوبة في ملف Excel')
                            ->content(view('filament.components.import-required-fields')),
                    ])
            ])
            ->action(function (array $data) {
                try {
                    $filePath = storage_path('app/public/' . $data['excel_file']);
                    
                    if (!file_exists($filePath)) {
                        throw new \Exception('الملف غير موجود');
                    }

                    // استخدام StudentsImport المحسنة
                    $import = new \App\Imports\StudentsImport(
                        auth()->user()?->school_id,
                        $data['default_branch_id'] ?? null,
                        $data['import_mode']
                    );

                    DB::beginTransaction();

                    // تنفيذ الاستيراد
                    \Maatwebsite\Excel\Facades\Excel::import($import, $filePath);
                    
                    DB::commit();

                    // حذف الملف المؤقت
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }

                    // عد النتائج من قاعدة البيانات
                    $totalStudents = Student::where('school_id', auth()->user()?->school_id)->count();
                    
                    $message = "تم إنجاز عملية الاستيراد بنجاح\n";
                    $message .= "إجمالي عدد الطلاب في النظام: {$totalStudents}";
                    
                    $errors = $import->getErrors();
                    
                    if (!empty($errors)) {
                        $message .= "\n\nملاحظات ومشاكل:\n" . implode("\n", array_slice($errors, 0, 5));
                        if (count($errors) > 5) {
                            $message .= "\n... و " . (count($errors) - 5) . " ملاحظة أخرى";
                        }
                        
                        Notification::make()
                            ->title('تم الاستيراد مع بعض الملاحظات')
                            ->body($message)
                            ->warning()
                            ->persistent()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('نجح الاستيراد')
                            ->body($message)
                            ->success()
                            ->send();
                    }

                } catch (\Exception $e) {
                    DB::rollBack();

                    // حذف الملف المؤقت في حالة الخطأ أيضاً
                    if (isset($filePath) && file_exists($filePath)) {
                        unlink($filePath);
                    }

                    Notification::make()
                        ->title('خطأ في الاستيراد')
                        ->body('حدث خطأ أثناء استيراد البيانات: ' . $e->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();

                    throw $e;
                }
            });
    }
}
