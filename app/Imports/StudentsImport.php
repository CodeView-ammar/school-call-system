<?php
namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try {
            return new Student([
                'code'          => $row['كود_الطالب'],
                'student_number'=> $row['الرقم_الأكاديمي'],
                'name_ar'       => $row['الاسم_بالعربية'],
                'name_en'       => $row['الاسم_بالإنجليزية'],
                'national_id'   => $row['رقم_الهوية'],
                'date_of_birth' => $row['تاريخ_الميلاد'],
                'gender'        => $row['الجنس'],
                'nationality'   => $row['الجنسية'],
                'is_active'     => $row['نشط'] === 'نعم' ? 1 : 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Import Error: ' . $e->getMessage());
            return null;
        }
    }
}
