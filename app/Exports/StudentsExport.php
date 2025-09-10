<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected $filters;
    
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Student::query()->with([
            'school', 
            'branch', 
            'academicBand', 
            'gradeClass',
            'bus',
            'guardians' => function ($q) {
                $q->orderBy('guardian_student.is_primary', 'desc');
            }
        ]);
        
        // تطبيق الفلاتر
        if (isset($this->filters['school_id']) && $this->filters['school_id']) {
            $query = $query->where('school_id', $this->filters['school_id']);
        }
        
        if (isset($this->filters['branch_id']) && $this->filters['branch_id']) {
            $query = $query->where('branch_id', $this->filters['branch_id']);
        }
        
        if (isset($this->filters['academic_band_id']) && $this->filters['academic_band_id']) {
            $query = $query->where('academic_band_id', $this->filters['academic_band_id']);
        }
        
        if (isset($this->filters['grade_class_id']) && $this->filters['grade_class_id']) {
            $query = $query->where('grade_class_id', $this->filters['grade_class_id']);
        }
        
        if (isset($this->filters['gender']) && $this->filters['gender']) {
            $query = $query->where('gender', $this->filters['gender']);
        }
        
        if (isset($this->filters['is_active']) && $this->filters['is_active'] !== null) {
            $query = $query->where('is_active', $this->filters['is_active']);
        }
        
        if (isset($this->filters['bus_id']) && $this->filters['bus_id']) {
            $query = $query->where('bus_id', $this->filters['bus_id']);
        }
        
        // فلتر حسب التاريخ إذا كان موجود
        if (isset($this->filters['date_from']) && $this->filters['date_from']) {
            $query = $query->where('date_of_birth', '>=', $this->filters['date_from']);
        }
        
        if (isset($this->filters['date_to']) && $this->filters['date_to']) {
            $query = $query->where('date_of_birth', '<=', $this->filters['date_to']);
        }
        
        return $query->orderBy('name_ar');
    }

    public function headings(): array
    {
        return [
            'كود الطالب',
            'الرقم الأكاديمي',
            'الاسم بالعربية',
            'الاسم بالإنجليزية',
            'رقم الهوية',
            'تاريخ الميلاد',
            'الجنس',
            'الجنسية',
            'المدرسة',
            'الفرع',
            'الفرقة الأكاديمية',
            'الفصل',
            'العنوان بالعربية',
            'خط العرض',
            'خط الطول',
            'ملاحظات طبية',
            'مكان الاستقلال',
            'كود الحافلة',
            'نشط',
            'ولي الأمر الأول',
            'هاتف ولي الأمر الأول',
            'ولي الأمر الثاني',
            'هاتف ولي الأمر الثاني'
        ];
    }

    public function map($student): array
    {
        // الحصول على أولياء الأمور
        $primaryGuardian = $student->guardians->where('pivot.is_primary', true)->first();
        $secondaryGuardian = $student->guardians->where('pivot.is_primary', false)->first();
        
        return [
            $student->code ?? '',
            $student->student_number ?? '',
            $student->name_ar ?? '',
            $student->name_en ?? '',
            $student->national_id ?? '',
            $student->date_of_birth?->format('Y-m-d') ?? '',
            $student->gender === 'female' ? 'أنثى' : 'ذكر',
            $student->nationality ?? '',
            $student->school?->name_ar ?? '',
            $student->branch?->name_ar ?? '',
            $student->academicBand?->name_ar ?? '',
            $student->gradeClass?->name_ar ?? '',
            $student->address_ar ?? '',
            $student->latitude ?? '',
            $student->longitude ?? '',
            $student->medical_notes ?? '',
            $student->pickup_location ?? '',
            $student->bus?->code ?? '',
            $student->is_active ? 'نعم' : 'لا',
            $primaryGuardian?->name_ar ?? '',
            $primaryGuardian?->phone ?? '',
            $secondaryGuardian?->name_ar ?? '',
            $secondaryGuardian?->phone ?? ''
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        $headerColumnCount = count($this->headings());
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($headerColumnCount);
        
        // تنسيق العناوين
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
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
        
        // تنسيق البيانات
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            $sheet->getStyle("A2:{$lastColumn}" . $highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            
            // تلوين الصفوف بالتناوب
            for ($i = 2; $i <= $highestRow; $i++) {
                if ($i % 2 == 0) {
                    $sheet->getStyle("A{$i}:{$lastColumn}{$i}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8F9FA']
                        ]
                    ]);
                }
            }
        }
        
        // ضبط ارتفاع الصف الأول
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        return [];
    }
    
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_TEXT, // National ID
            'F' => NumberFormat::FORMAT_DATE_YYYYMMDD, // Date of Birth  
            'N' => NumberFormat::FORMAT_NUMBER_00, // Latitude
            'O' => NumberFormat::FORMAT_NUMBER_00, // Longitude
        ];
    }
}
