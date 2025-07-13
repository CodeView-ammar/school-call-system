<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use App\Models\Branch;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
                            ->preserveFilenames(),

                        Grid::make(2)->schema([
                            Select::make('default_branch_id')
                                ->label('الفرع الافتراضي')
                                ->required()
                                ->options(function () {
                                    $query = Branch::query();
                                    if (auth()->user()?->school_id) {
                                        $query->where('school_id', auth()->user()->school_id);
                                    }
                                    return $query->pluck('name_ar', 'id');
                                })
                                ->searchable(),

                            Select::make('import_mode')
                                ->label('وضع الاستيراد')
                                ->required()
                                ->options([
                                    'create_only' => 'إنشاء جديد فقط',
                                    'update_existing' => 'تحديث الموجود وإنشاء جديد',
                                    'replace_all' => 'استبدال الكل (خطر)',
                                ])
                                ->default('create_only'),
                        ]),

                        Placeholder::make('format_info')
                            ->label('تنسيق الملف المطلوب')
                            ->content(function () {
                                return view('filament.components.import-format-info');
                            }),
                    ])
            ])
            ->action(function (array $data) {
                try {
                    $filePath = storage_path('app/public/' . $data['excel_file']);
                    
                    if (!file_exists($filePath)) {
                        throw new \Exception('الملف غير موجود');
                    }

                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();

                    // إزالة الهيدر
                    array_shift($rows);

                    $imported = 0;
                    $updated = 0;
                    $errors = [];

                    DB::beginTransaction();

                    foreach ($rows as $index => $row) {
                        $rowNumber = $index + 2; // +2 لأن الهيدر محذوف والمصفوفة تبدأ من 0
                        
                        try {
                            // التحقق من البيانات المطلوبة
                            if (empty($row[0]) || empty($row[1])) { // رقم الطالب والاسم
                                continue;
                            }

                            $studentData = [
                                'student_number' => $row[0],
                                'name_ar' => $row[1],
                                'name_en' => $row[2] ?? null,
                                'gender' => strtolower($row[3]) === 'أنثى' || strtolower($row[3]) === 'female' ? 'female' : 'male',
                                'national_id' => $row[4] ?? null,
                                'phone' => $row[5] ?? null,
                                'address_ar' => $row[6] ?? null,
                                'branch_id' => $data['default_branch_id'],
                                'school_id' => auth()->user()?->school_id ?? 1,
                                'is_active' => true,
                            ];

                            if ($data['import_mode'] === 'update_existing') {
                                $student = Student::updateOrCreate(
                                    ['student_number' => $studentData['student_number']],
                                    $studentData
                                );
                                
                                if ($student->wasRecentlyCreated) {
                                    $imported++;
                                } else {
                                    $updated++;
                                }
                            } else {
                                // التحقق من عدم وجود الطالب
                                if (Student::where('student_number', $studentData['student_number'])->exists()) {
                                    $errors[] = "الصف {$rowNumber}: رقم الطالب {$studentData['student_number']} موجود مسبقاً";
                                    continue;
                                }

                                Student::create($studentData);
                                $imported++;
                            }
                        } catch (\Exception $e) {
                            $errors[] = "الصف {$rowNumber}: " . $e->getMessage();
                        }
                    }

                    DB::commit();

                    // حذف الملف المؤقت
                    unlink($filePath);

                    $message = "تم استيراد {$imported} طالب جديد";
                    if ($updated > 0) {
                        $message .= " وتحديث {$updated} طالب";
                    }

                    if (!empty($errors)) {
                        $message .= "\n\nأخطاء:\n" . implode("\n", array_slice($errors, 0, 5));
                        if (count($errors) > 5) {
                            $message .= "\n... و " . (count($errors) - 5) . " أخطاء أخرى";
                        }
                    }

                    Notification::make()
                        ->title($imported > 0 || $updated > 0 ? 'تم الاستيراد بنجاح' : 'لم يتم استيراد أي بيانات')
                        ->body($message)
                        ->color($imported > 0 || $updated > 0 ? 'success' : 'warning')
                        ->duration(10000)
                        ->send();

                } catch (\Exception $e) {
                    DB::rollBack();
                    
                    Notification::make()
                        ->title('خطأ في الاستيراد')
                        ->body('حدث خطأ أثناء استيراد البيانات: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->modalWidth('lg')
            ->modalSubmitActionLabel('بدء الاستيراد')
            ->modalCancelActionLabel('إلغاء');
    }
}