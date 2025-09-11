<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Branch;
use App\Models\AcademicBand;
use App\Models\GradeClass;
use App\Models\Bus;
use App\Models\Guardian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $schoolId;
    private $defaultBranchId;
    private $importMode;
    private $errors = [];
    private $successCount = 0;
    private $updateCount = 0;
    private $skipCount = 0;

    public function __construct($schoolId, $defaultBranchId = null, $importMode = 'create_only')
    {
        $this->schoolId = $schoolId;
        $this->defaultBranchId = $defaultBranchId;
        $this->importMode = $importMode;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because index starts at 0 and we have header row
                
                try {
                    $this->processRow($row->toArray(), $rowNumber);
                } catch (\Exception $e) {
                    $this->errors[] = "صف {$rowNumber}: {$e->getMessage()}";
                    Log::error("Error processing row {$rowNumber}: " . $e->getMessage(), [
                        'row_data' => $row->toArray()
                    ]);
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function processRow(array $row, int $rowNumber)
    {
        try {
            // تنظيف البيانات وإزالة المفاتيح الفارغة
            $originalRow = $row;
            $row = array_filter($row, function($value, $key) {
                return !is_null($value) && $value !== '' && !is_null($key) && $key !== '';
            }, ARRAY_FILTER_USE_BOTH);

            // التحقق من وجود البيانات الأساسية
            $studentCode = $this->getStudentCode($row);
            $studentName = $this->getStudentName($row);

            if (empty($studentCode)) {
                throw new \Exception("كود الطالب مطلوب");
            }

            if (empty($studentName)) {
                throw new \Exception("اسم الطالب مطلوب");
            }

            // التحقق من وجود الفرع
            $branchId = $this->getBranchId($row) ?? $this->defaultBranchId;
            if (!$branchId) {
                throw new \Exception("الفرع مطلوب - يرجى تحديد فرع افتراضي أو إضافة فرع في البيانات");
            }

            // البحث عن الطالب الموجود
            $existingStudent = Student::where('school_id', $this->schoolId)
                ->where('code', $studentCode)
                ->first();

            // إذا كان الطالب موجود ووضع الاستيراد "إنشاء جديد فقط"
            if ($existingStudent && $this->importMode === 'create_only') {
                $this->skipCount++;
                $this->errors[] = "صف {$rowNumber}: الطالب {$studentName} (كود: {$studentCode}) موجود مسبقاً";
                return;
            }

            // تحضير بيانات الطالب
            $studentData = $this->prepareStudentData($row, $rowNumber);

            if ($existingStudent && $this->importMode === 'update_existing') {
                // تحديث الطالب الموجود
                $existingStudent->update($studentData);
                $student = $existingStudent;
                $this->updateCount++;
                Log::info("Updated student", ['student_id' => $student->id, 'code' => $studentCode]);
            } else {
                // إنشاء طالب جديد
                $student = Student::create($studentData);
                $this->successCount++;
                Log::info("Created student", ['student_id' => $student->id, 'code' => $studentCode]);
            }

            // معالجة بيانات أولياء الأمور
            $this->processGuardians($student, $row, $rowNumber);

        } catch (\Exception $e) {
            Log::error("Error processing row {$rowNumber}: " . $e->getMessage(), [
                'row_data' => $originalRow ?? $row,
                'exception' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getStudentCode(array $row): ?string
    {
        // البحث في جميع المفاتيح الممكنة لكود الطالب
        $codePossibleKeys = [
            'code', 'student_code', 'كود_الطالب', 'الكود', 'كود', 'رقم_الطالب', 'student_number',
            'student_id', 'id', 'معرف_الطالب', 'رقم', 'Student Code', 'Code', 'Student ID'
        ];
        
        foreach ($codePossibleKeys as $key) {
            if (isset($row[$key]) && !empty(trim($row[$key]))) {
                return trim($row[$key]);
            }
        }
        
        // البحث بالمفاتيح التي تحتوي على كلمات مشابهة
        foreach ($row as $key => $value) {
            $keyLower = strtolower(trim($key));
            if (!empty(trim($value)) && (
                strpos($keyLower, 'code') !== false ||
                strpos($keyLower, 'كود') !== false ||
                strpos($keyLower, 'رقم') !== false ||
                strpos($keyLower, 'id') !== false
            )) {
                return trim($value);
            }
        }
        
        return null;
    }

    private function getStudentName(array $row): ?string
    {
        // البحث في جميع المفاتيح الممكنة لاسم الطالب
        $namePossibleKeys = [
            'name_ar', 'الاسم_العربي', 'name_en', 'الاسم_الانجليزي', 'اسم_الطالب', 'student_name',
            'name', 'اسم', 'الاسم', 'Student Name', 'Name', 'الطالب', 'طالب', 'full_name',
            'student_name_ar', 'student_name_en', 'اسم_كامل'
        ];
        
        foreach ($namePossibleKeys as $key) {
            if (isset($row[$key]) && !empty(trim($row[$key]))) {
                return trim($row[$key]);
            }
        }
        
        // البحث بالمفاتيح التي تحتوي على كلمات مشابهة
        foreach ($row as $key => $value) {
            $keyLower = strtolower(trim($key));
            if (!empty(trim($value)) && (
                strpos($keyLower, 'name') !== false ||
                strpos($keyLower, 'اسم') !== false ||
                strpos($keyLower, 'طالب') !== false ||
                strpos($keyLower, 'student') !== false
            )) {
                return trim($value);
            }
        }
        
        return null;
    }

    private function prepareStudentData(array $row, int $rowNumber): array
    {
        // الحقول الأساسية المطلوبة
        $studentCode = $this->getStudentCode($row);
        $studentNameAr = $this->getStudentName($row);
        
        $data = [
            'school_id' => $this->schoolId,
            'code' => $studentCode,
            'is_active' => true, // قيمة افتراضية
        ];

        // اسم الطالب (مطلوب - إما عربي أو إنجليزي)
        if (!empty($studentNameAr)) {
            $data['name_ar'] = $studentNameAr;
        }
        
        // الاسم الإنجليزي إذا كان متوفراً
        $nameEn = $this->getFieldValue($row, ['name_en', 'الاسم_الانجليزي', 'student_name_en']);
        if (!empty($nameEn)) {
            $data['name_en'] = $nameEn;
        }

        // الحقول الاختيارية - فقط إذا كانت موجودة ولها قيم
        $optionalFields = [
            'student_number' => ['student_number', 'رقم_الطالب', 'الرقم_الاكاديمي'],
            'national_id' => ['national_id', 'الرقم_الوطني', 'رقم_الهوية'],
            'date_of_birth' => ['date_of_birth', 'تاريخ_الميلاد'],
            'gender' => ['gender', 'الجنس'],
            'nationality' => ['nationality', 'الجنسية'],
            'address_ar' => ['address_ar', 'العنوان_العربي', 'العنوان'],
            'address_en' => ['address_en', 'العنوان_الانجليزي'],
            'latitude' => ['latitude', 'خط_العرض'],
            'longitude' => ['longitude', 'خط_الطول'],
            'medical_notes' => ['medical_notes', 'الملاحظات_الطبية'],
            'emergency_contact' => ['emergency_contact', 'جهة_الاتصال_الطارئ'],
            'pickup_location' => ['pickup_location', 'مكان_الاستقلال'],
        ];

        foreach ($optionalFields as $field => $keys) {
            $value = $this->getFieldValue($row, $keys);
            if (!empty($value)) {
                // معالجة خاصة لتاريخ الميلاد
                if ($field === 'date_of_birth') {
                    try {
                        $data[$field] = date('Y-m-d', strtotime($value));
                    } catch (\Exception $e) {
                        // تجاهل التاريخ غير الصحيح
                        continue;
                    }
                } else {
                    $data[$field] = $value;
                }
            }
        }

        // معالجة الفرع (مطلوب)
        $branchId = $this->getBranchId($row) ?? $this->defaultBranchId;
        if ($branchId) {
            $data['branch_id'] = $branchId;
        }

        // معالجة المرحلة الأكاديمية (اختياري)
        $academicBandId = $this->getAcademicBandId($row);
        if ($academicBandId) {
            $data['academic_band_id'] = $academicBandId;
        }

        // معالجة الصف (اختياري)
        $gradeClassId = $this->getGradeClassId($row);
        if ($gradeClassId) {
            $data['grade_class_id'] = $gradeClassId;
        }

        // معالجة الحافلة (اختياري)
        $busId = $this->getBusId($row);
        if ($busId) {
            $data['bus_id'] = $busId;
        }

        // معالجة حالة النشاط
        $isActive = $this->getFieldValue($row, ['is_active', 'نشط', 'active','نعم','yes']);
        if (!empty($isActive)) {
            $data['is_active'] = filter_var($isActive, FILTER_VALIDATE_BOOLEAN);
        }
        $data['is_active'] =True;
        return $data;
    }

    /**
     * استخراج قيمة حقل من مفاتيح متعددة
     */
    private function getFieldValue(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && !empty(trim($row[$key]))) {
                return trim($row[$key]);
            }
        }
        return null;
    }

    private function getBranchId(array $row): ?int
    {
        $branchName = $row['branch_name'] ?? $row['اسم_الفرع'] ?? null;
        $branchId = $row['branch_id'] ?? $row['معرف_الفرع'] ?? null;

        if ($branchId) {
            $branch = Branch::where('id', $branchId)
                ->where('school_id', $this->schoolId)
                ->first();
            if ($branch) return $branch->id;
        }

        if ($branchName) {
            $branch = Branch::where('school_id', $this->schoolId)
                ->where(function($query) use ($branchName) {
                    $query->where('name_ar', $branchName)
                          ->orWhere('name_en', $branchName);
                })
                ->first();
            if ($branch) return $branch->id;
        }

        return null;
    }

    private function getAcademicBandId(array $row): ?int
    {
        $bandName = $row['academic_band_name'] ?? $row['اسم_المرحلة'] ?? null;
        $bandId = $row['academic_band_id'] ?? $row['معرف_المرحلة'] ?? null;

        if ($bandId) {
            $band = AcademicBand::where('id', $bandId)
                ->where('school_id', $this->schoolId)
                ->first();
            if ($band) return $band->id;
        }

        if ($bandName) {
            $band = AcademicBand::where('school_id', $this->schoolId)
                ->where(function($query) use ($bandName) {
                    $query->where('name_ar', $bandName)
                          ->orWhere('name_en', $bandName);
                })
                ->first();
            if ($band) return $band->id;
        }

        return null;
    }

    private function getGradeClassId(array $row): ?int
    {
        $className = $row['grade_class_name'] ?? $row['اسم_الصف'] ?? null;
        $classId = $row['grade_class_id'] ?? $row['معرف_الصف'] ?? null;

        if ($classId) {
            $class = GradeClass::where('id', $classId)
                ->where('school_id', $this->schoolId)
                ->first();
            if ($class) return $class->id;
        }

        if ($className) {
            $class = GradeClass::where('school_id', $this->schoolId)
                ->where(function($query) use ($className) {
                    $query->where('name_ar', $className)
                          ->orWhere('name_en', $className);
                })
                ->first();
            if ($class) return $class->id;
        }

        return null;
    }

    private function getBusId(array $row): ?int
    {
        $busCode = $row['bus_code'] ?? $row['كود_الحافلة'] ?? null;
        $busId = $row['bus_id'] ?? $row['معرف_الحافلة'] ?? null;

        if ($busId) {
            $bus = Bus::where('id', $busId)
                ->where('school_id', $this->schoolId)
                ->first();
            if ($bus) return $bus->id;
        }

        if ($busCode) {
            $bus = Bus::where('school_id', $this->schoolId)
                ->where('code', $busCode)
                ->first();
            if ($bus) return $bus->id;
        }

        return null;
    }

    private function processGuardians(Student $student, array $row, int $rowNumber)
    {
        // معالجة ولي الأمر الأول
        $this->processGuardian($student, $row, 1, true);
        
        // معالجة ولي الأمر الثاني
        $this->processGuardian($student, $row, 2, false);
    }

    private function processGuardian(Student $student, array $row, int $guardianNumber, bool $isPrimary)
    {
        $prefix = $guardianNumber === 1 ? '' : '2_';
        
        $guardianData = [
            'name_ar' => $row[$prefix . 'guardian_name_ar'] ?? $row[$prefix . 'اسم_ولي_الامر_عربي'] ?? null,
            'name_en' => $row[$prefix . 'guardian_name_en'] ?? $row[$prefix . 'اسم_ولي_الامر_انجليزي'] ?? null,
            'phone' => $row[$prefix . 'guardian_phone'] ?? $row[$prefix . 'هاتف_ولي_الامر'] ?? null,
            'email' => $row[$prefix . 'guardian_email'] ?? $row[$prefix . 'ايميل_ولي_الامر'] ?? null,
            'national_id' => $row[$prefix . 'guardian_national_id'] ?? $row[$prefix . 'رقم_هوية_ولي_الامر'] ?? null,
            'relationship' => $row[$prefix . 'guardian_relationship'] ?? $row[$prefix . 'صلة_القرابة'] ?? null,
        ];

        // إزالة القيم الفارغة
        $guardianData = array_filter($guardianData, function($value) {
            return !is_null($value) && $value !== '';
        });

        if (empty($guardianData['name_ar']) && empty($guardianData['name_en'])) {
            return; // لا توجد بيانات ولي أمر
        }

        $guardianData['school_id'] = $this->schoolId;

        // البحث عن ولي الأمر الموجود
        $guardian = null;
        
        if (!empty($guardianData['phone'])) {
            $guardian = Guardian::where('school_id', $this->schoolId)
                ->where('phone', $guardianData['phone'])
                ->first();
        }
        
        if (!$guardian && !empty($guardianData['national_id'])) {
            $guardian = Guardian::where('school_id', $this->schoolId)
                ->where('national_id', $guardianData['national_id'])
                ->first();
        }

        if ($guardian) {
            // تحديث بيانات ولي الأمر الموجود
            $guardian->update($guardianData);
        } else {
            // إنشاء ولي أمر جديد
            $guardian = Guardian::create($guardianData);
        }

        // ربط ولي الأمر بالطالب
        if (!$student->guardians()->where('guardian_id', $guardian->id)->exists()) {
            $student->guardians()->attach($guardian->id, [
                'is_primary' => $isPrimary && $guardianNumber === 1
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // لا نضع قواعد صارمة هنا لأننا نتعامل مع أشكال مختلفة من البيانات
        ];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getUpdateCount(): int
    {
        return $this->updateCount;
    }

    public function getSkipCount(): int
    {
        return $this->skipCount;
    }
}
