<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Student::with(['school', 'branch', 'academicBand', 'bus'])->get();
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
            'الحافلة',
            'نشط',
        ];
    }

    public function map($student): array
    {
        return [
            $student->code,
            $student->student_number,
            $student->name_ar,
            $student->name_en,
            $student->national_id,
            $student->date_of_birth?->format('Y-m-d'),
            $student->gender,
            $student->nationality,
            $student->school?->name_ar,
            $student->branch?->name_ar,
            $student->academicBand?->name_ar,
            $student->bus?->code,
            $student->is_active ? 'نعم' : 'لا',
        ];
    }
}
