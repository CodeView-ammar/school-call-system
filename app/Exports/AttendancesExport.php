<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class AttendancesExport implements FromCollection, WithHeadings
{
    public ?string $from;
    public ?string $to;

    public function __construct(?string $from = null, ?string $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function collection(): Collection
    {
        $query = Attendance::with(['student', 'branch', 'gradeClass']);  // جلب العلاقات

        if ($this->from && $this->to) {
            $query->whereBetween('attendance_date', [$this->from, $this->to]);
        }

        $attendances = $query->get();

        // إعادة تشكيل البيانات بحيث تحتوي على الأسماء بدلاً من IDs
        return $attendances->map(function ($attendance) {
            return [
                'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
                'student_name'    => $attendance->student?->name_ar ?? 'غير محدد',
                'branch_name'     => $attendance->branch?->name_ar ?? 'غير محدد',
                'grade_class'     => $attendance->gradeClass?->name_ar ?? 'غير محدد',
                'status'          => $attendance->status_label,
                'notes'           => $attendance->notes ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'تاريخ الحضور',
            'اسم الطالب',
            'الفرع',
            'الصف الدراسي',
            'الحالة',
            'ملاحظات',
        ];
    }
}
