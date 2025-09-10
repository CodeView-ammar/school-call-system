<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use App\Models\Branch;
use App\Models\AcademicBand;
use App\Models\GradeClass;
use App\Models\Bus;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DownloadStudentTemplateAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'download_template';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('تحميل قالب الاستيراد')
            ->icon('heroicon-o-document-arrow-down')
            ->color('info')
            ->form([
                Section::make('إعدادات القالب')
                    ->description('اختر الإعدادات المناسبة لإنشاء قالب مخصص')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('include_sample_data')
                                ->label('تضمين بيانات عينة')
                                ->options([
                                    'yes' => 'نعم، أضف 3 طلاب كمثال',
                                    'no' => 'لا، قالب فارغ فقط'
                                ])
                                ->default('yes')
                                ->helperText('البيانات العينة توضح كيفية تعبئة القالب بشكل صحيح'),

                            Select::make('template_language')
                                ->label('لغة العناوين')
                                ->options([
                                    'ar' => 'العربية',
                                    'en' => 'الإنجليزية'
                                ])
                                ->default('ar')
                                ->helperText('اختر لغة عناوين الأعمدة'),
                        ])
                    ])
            ])
            ->action(function (array $data) {
                try {
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle('قالب الطلاب');

                    // تحديد الأعمدة بأسماء اللغة الإنجليزية المطلوبة للاستيراد
                    $headers = [
                        'student_code',
                        'student_name_ar',
                        'branch_name',
                        'academic_band_name',
                        'grade_class_name',
                        'student_number',
                        'student_name_en',
                        'national_id',
                        'date_of_birth',
                        'gender',
                        'nationality',
                        'address_ar',
                        'address_en',
                        'latitude',
                        'longitude',
                        'medical_notes',
                        'emergency_contact',
                        'pickup_location',
                        'bus_code',
                        'is_active',
                        'guardian_1_name',
                        'guardian_1_phone',
                        'guardian_1_relationship',
                        'guardian_2_name',
                        'guardian_2_phone',
                        'guardian_2_relationship'
                    ];

                    // إضافة العناوين الإنجليزية
                    $sheet->fromArray([$headers], null, 'A1');

                    // إضافة صف توضيحي بالعربية
                    $arabicHeaders = [
                        'كود الطالب (مطلوب)',
                        'اسم الطالب بالعربية (مطلوب)',
                        'اسم الفرع (مطلوب)',
                        'اسم الفرقة الأكاديمية (مطلوب)',
                        'اسم الفصل الدراسي (مطلوب)',
                        'الرقم الأكاديمي',
                        'اسم الطالب بالإنجليزية',
                        'رقم الهوية',
                        'تاريخ الميلاد (YYYY-MM-DD)',
                        'الجنس (ذكر/أنثى)',
                        'الجنسية',
                        'العنوان بالعربية',
                        'العنوان بالإنجليزية',
                        'خط العرض',
                        'خط الطول',
                        'الملاحظات الطبية',
                        'جهة اتصال الطوارئ',
                        'مكان الاستقلال',
                        'كود الحافلة',
                        'نشط (نعم/لا)',
                        'اسم ولي الأمر الأول',
                        'هاتف ولي الأمر الأول',
                        'علاقة ولي الأمر الأول',
                        'اسم ولي الأمر الثاني',
                        'هاتف ولي الأمر الثاني',
                        'علاقة ولي الأمر الثاني'
                    ];
                    $sheet->fromArray([$arabicHeaders], null, 'A2');

                    // بيانات الأمثلة
                    $sampleData = [
                        [
                            'STD001',
                            'أحمد محمد علي',
                            'الفرع الرئيسي',
                            'المرحلة الابتدائية',
                            'الصف الأول أ',
                            'AC001',
                            'Ahmed Mohammed Ali',
                            '1234567890',
                            '2015-01-15',
                            'ذكر',
                            'السعودية',
                            'الرياض - حي النرجس',
                            'Riyadh - Al Narjis District',
                            '24.7136',
                            '46.6753',
                            'لا يوجد',
                            'والد الطالب: 0501234567',
                            'بوابة المدرسة الرئيسية',
                            'BUS001',
                            'نعم',
                            'محمد علي أحمد',
                            '0501234567',
                            'أب',
                            'فاطمة سعد محمد',
                            '0507654321',
                            'أم'
                        ],
                        [
                            'STD002',
                            'فاطمة سعد أحمد',
                            'الفرع الرئيسي',
                            'المرحلة الابتدائية',
                            'الصف الثاني ب',
                            'AC002',
                            'Fatima Saad Ahmed',
                            '0987654321',
                            '2014-06-20',
                            'أنثى',
                            'السعودية',
                            'الرياض - حي الملقا',
                            'Riyadh - Al Malqa District',
                            '24.7500',
                            '46.6000',
                            'حساسية من الفول السوداني',
                            'والدة الطالبة: 0509876543',
                            'المدخل الجانبي',
                            'BUS002',
                            'نعم',
                            'سعد أحمد محمد',
                            '0509876543',
                            'أب',
                            'نورا خالد علي',
                            '0501122334',
                            'أم'
                        ]
                    ];

                    // إضافة البيانات التوضيحية
                    if (!empty($sampleData)) {
                        $row = 3; // البدء من الصف الثالث بعد العناوين والترجمة
                        foreach ($sampleData as $data) {
                            $column = 1;
                            foreach ($data as $value) {
                                $sheet->setCellValueByColumnAndRow($column, $row, $value);
                                $column++;
                            }
                            $row++;
                        }
                    }

                    // تنسيق العناوين
                    $headerRange = 'A1:' . Coordinate::stringFromColumnIndex(count($headers)) . '1';
                    $sheet->getStyle($headerRange)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4472C4']
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF'],
                            'size' => 12
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ]);

                    // تنسيق صف الترجمة
                    $translationHeaderRange = 'A2:' . Coordinate::stringFromColumnIndex(count($arabicHeaders)) . '2';
                    $sheet->getStyle($translationHeaderRange)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'C6E0B4']
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000'],
                            'size' => 10
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ]);

                    // ضبط عرض الأعمدة
                    foreach (range(1, count($headers)) as $col) {
                        $sheet->getColumnDimensionByColumn($col)->setWidth(20);
                    }

                    // ضبط ارتفاع الصف الأول والثاني
                    $sheet->getRowDimension(1)->setRowHeight(25);
                    $sheet->getRowDimension(2)->setRowHeight(20);

                    // إضافة ورقة تعليمات
                    $instructionsSheet = $spreadsheet->createSheet();
                    $instructionsSheet->setTitle('تعليمات الاستخدام');

                    $instructions = [
                        'تعليمات استخدام قالب استيراد الطلاب',
                        '',
                        '1. الحقول المطلوبة (يجب تعبئتها):',
                        '   - كود الطالب: رمز فريد لكل طالب',
                        '   - اسم الطالب بالعربية: الاسم الكامل',
                        '   - اسم الفرع: يجب أن يطابق اسم فرع موجود في النظام',
                        '   - اسم الفرقة الأكاديمية: يجب أن يطابق اسم موجود في النظام',
                        '   - اسم الفصل الدراسي: يجب أن يطابق اسم موجود في النظام',
                        '',
                        '2. تنسيق البيانات:',
                        '   - تاريخ الميلاد: YYYY-MM-DD (مثال: 2010-05-15)',
                        '   - الجنس: ذكر، أنثى، male، أو female',
                        '   - نشط: نعم، لا، yes، أو no',
                        '   - الإحداثيات: أرقام عشرية (مثال: 24.7136)',
                        '',
                        '3. البيانات المرجعية:',
                        '   - تأكد من أن أسماء الفروع والفصول موجودة في الأوراق المرجعية',
                        '   - استخدم الأسماء الصحيحة كما هي موضحة في الأوراق المرجعية',
                        '',
                        '4. أولياء الأمور:',
                        '   - يمكن إضافة ولي أمر واحد أو اثنين لكل طالب',
                        '   - علاقة ولي الأمر: أب، أم، جد، جدة، عم، خال، إلخ',
                        '',
                        '5. نصائح مهمة:',
                        '   - لا تغير ترتيب الأعمدة',
                        '   - لا تحذف السطر الأول (العناوين)',
                        '   - تأكد من عدم وجود مسافات زائدة في البيانات',
                        '   - استخدم الترميز UTF-8 عند حفظ الملف',
                        '',
                        '6. أكواد الحافلات:',
                        '   - اتركها فارغة إذا لم يكن الطالب يستخدم النقل المدرسي',
                        '   - استخدم أكواد الحافلات الموجودة في النظام فقط'
                    ];

                    $row = 1;
                    foreach ($instructions as $instruction) {
                        $instructionsSheet->setCellValue('A' . $row, $instruction);
                        if ($row === 1) {
                            $instructionsSheet->getStyle('A' . $row)->applyFromArray([
                                'font' => ['bold' => true, 'size' => 14],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                            ]);
                        }
                        $row++;
                    }

                    $instructionsSheet->getColumnDimension('A')->setWidth(80);

                    // إضافة أوراق مرجعية للبيانات المرجعية
                    $this->addReferenceSheets($spreadsheet);

                    // العودة إلى الورقة الرئيسية
                    $spreadsheet->setActiveSheetIndex(0);

                    // إنشاء الكاتب وتحميل الملف
                    $writer = new Xlsx($spreadsheet);
                    $filename = 'students_import_template_' . date('Y-m-d_H-i-s') . '.xlsx';

                    // إنشاء مجلد temp إذا لم يكن موجود
                    $tempDir = storage_path('app/temp');
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }

                    $tempPath = $tempDir . '/' . $filename;

                    $writer->save($tempPath);

                    Notification::make()
                        ->title('تم إنشاء القالب بنجاح')
                        ->body('تم تحميل قالب استيراد الطلاب')
                        ->success()
                        ->send();

                    return response()->download($tempPath, $filename)->deleteFileAfterSend();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('خطأ في إنشاء القالب')
                        ->body('حدث خطأ أثناء إنشاء القالب: ' . $e->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();

                    throw $e;
                }
            });
    }

    private function addReferenceSheets(Spreadsheet $spreadsheet): void
    {
        $schoolId = auth()->user()?->school_id;

        // ورقة الفروع
        $branchSheet = $spreadsheet->createSheet();
        $branchSheet->setTitle('الفروع المتاحة');
        $branchSheet->setCellValue('A1', 'أسماء الفروع المتاحة');
        $branchSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
        ]);

        $branches = Branch::where('school_id', $schoolId)->where('is_active', true)->get();
        $row = 2;
        foreach ($branches as $branch) {
            $branchSheet->setCellValue('A' . $row, $branch->name_ar);
            $row++;
        }
        $branchSheet->getColumnDimension('A')->setWidth(30);

        // ورقة الفرق الأكاديمية
        $bandSheet = $spreadsheet->createSheet();
        $bandSheet->setTitle('الفرق الأكاديمية');
        $bandSheet->setCellValue('A1', 'أسماء الفرق الأكاديمية المتاحة');
        $bandSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
        ]);

        $bands = AcademicBand::where('school_id', $schoolId)->where('is_active', true)->get();
        $row = 2;
        foreach ($bands as $band) {
            $bandSheet->setCellValue('A' . $row, $band->name_ar);
            $row++;
        }
        $bandSheet->getColumnDimension('A')->setWidth(30);

        // ورقة الفصول
        $classSheet = $spreadsheet->createSheet();
        $classSheet->setTitle('الفصول الدراسية');
        $classSheet->setCellValue('A1', 'أسماء الفصول الدراسية المتاحة');
        $classSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
        ]);

        $classes = GradeClass::where('school_id', $schoolId)->where('is_active', true)->get();
        $row = 2;
        foreach ($classes as $class) {
            $classSheet->setCellValue('A' . $row, $class->name_ar);
            $row++;
        }
        $classSheet->getColumnDimension('A')->setWidth(30);

        // ورقة الحافلات
        $busSheet = $spreadsheet->createSheet();
        $busSheet->setTitle('أكواد الحافلات');
        $busSheet->setCellValue('A1', 'أكواد الحافلات المتاحة');
        $busSheet->setCellValue('B1', 'اسم الحافلة');
        $busSheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
        ]);

        $buses = Bus::whereHas('branch', function($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->where('is_active', true)->get();

        $row = 2;
        foreach ($buses as $bus) {
            $busSheet->setCellValue('A' . $row, $bus->code);
            $busSheet->setCellValue('B' . $row, $bus->name_ar);
            $row++;
        }
        $busSheet->getColumnDimension('A')->setWidth(15);
        $busSheet->getColumnDimension('B')->setWidth(25);
    }
}