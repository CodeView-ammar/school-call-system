<?php
namespace App\Imports;

use App\Models\Student;
use App\Models\Branch;
use App\Models\AcademicBand;
use App\Models\GradeClass;
use App\Models\Bus;
use App\Models\Guardian;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;

class StudentsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, WithStartRow
{
    protected $schoolId;
    protected $defaultBranchId;
    protected $importMode;
    protected $errors = [];
    protected $requiredFields = [];
    
    public function __construct($schoolId = null, $defaultBranchId = null, $importMode = 'create_only')
    {
        $this->schoolId = $schoolId ?? auth()->user()?->school_id;
        $this->defaultBranchId = $defaultBranchId;
        $this->importMode = $importMode;
        $this->setRequiredFields();
    }

    public function startRow(): int
    {
        return 2;
    }
    
    /**
     * تحديد الحقول المطلوبة
     */
    private function setRequiredFields()
    {
        $this->requiredFields = [
            // الحقول الأساسية المطلوبة
            'student_code' => 'كود الطالب (مطلوب)',
            'student_name_ar' => 'اسم الطالب بالعربية (مطلوب)',
            'branch_name' => 'اسم الفرع (مطلوب)',
            'academic_band_name' => 'اسم الفرقة الأكاديمية (مطلوب)',
            'grade_class_name' => 'اسم الفصل الدراسي (مطلوب)',
            
            // الحقول الاختيارية
            'student_number' => 'الرقم الأكاديمي (اختياري)',
            'student_name_en' => 'اسم الطالب بالإنجليزية (اختياري)',
            'national_id' => 'رقم الهوية (اختياري)',
            'date_of_birth' => 'تاريخ الميلاد (اختياري) - تنسيق: YYYY-MM-DD',
            'gender' => 'الجنس (اختياري) - ذكر/أنثى',
            'nationality' => 'الجنسية (اختياري)',
            'address_ar' => 'العنوان بالعربية (اختياري)',
            'address_en' => 'العنوان بالإنجليزية (اختياري)',
            'latitude' => 'خط العرض (اختياري)',
            'longitude' => 'خط الطول (اختياري)',
            'medical_notes' => 'الملاحظات الطبية (اختياري)',
            'emergency_contact' => 'جهة اتصال الطوارئ (اختياري)',
            'pickup_location' => 'مكان الاستقلال (اختياري)',
            'bus_code' => 'كود الحافلة (اختياري)',
            'is_active' => 'نشط (اختياري) - نعم/لا',
            
            // أولياء الأمور
            'guardian_1_name' => 'اسم ولي الأمر الأول (اختياري)',
            'guardian_1_phone' => 'هاتف ولي الأمر الأول (اختياري)',
            'guardian_1_relationship' => 'علاقة ولي الأمر الأول (اختياري)',
            'guardian_2_name' => 'اسم ولي الأمر الثاني (اختياري)',
            'guardian_2_phone' => 'هاتف ولي الأمر الثاني (اختياري)',
            'guardian_2_relationship' => 'علاقة ولي الأمر الثاني (اختياري)',
        ];
    }

    /**
     * الحصول على الحقول المطلوبة للعرض
     */
    public function getRequiredFields()
    {
        return $this->requiredFields;
    }
    
    public function model(array $row)
    {
        try {
            // تنظيف البيانات
            $row = array_map(function($value) {
                return is_string($value) ? trim($value) : $value;
            }, $row);
            
            // التحقق من البيانات الأساسية المطلوبة
            $validationResult = $this->validateRequiredFields($row);
            if (!$validationResult['valid']) {
                $this->errors[] = $validationResult['message'];
                return null;
            }
            
            // البحث عن أو إنشاء الطالب
            $studentData = $this->prepareStudentData($row);
            
            if ($this->importMode === 'update_existing') {
                $student = Student::updateOrCreate(
                    ['code' => $studentData['code'], 'school_id' => $this->schoolId],
                    $studentData
                );
            } else {
                // التحقق من عدم وجود الطالب
                if (Student::where('code', $studentData['code'])
                    ->where('school_id', $this->schoolId)->exists()) {
                    $this->errors[] = 'الطالب بكود ' . $studentData['code'] . ' موجود بالفعل';
                    return null;
                }
                
                $student = Student::create($studentData);
            }
            
            // إضافة أولياء الأمور
            $this->handleGuardians($student, $row);
            
            return $student;
            
        } catch (\Exception $e) {
            $this->errors[] = 'خطأ في استيراد البيانات: ' . $e->getMessage();
            Log::error('Import Error: ' . $e->getMessage() . ' Row: ' . json_encode($row));
            return null;
        }
    }

    /**
     * التحقق من الحقول المطلوبة
     */
    private function validateRequiredFields(array $row)
    {
        $missingFields = [];
        
        // التحقق من الحقول الأساسية المطلوبة
        if (empty($row['student_code'])) {
            $missingFields[] = 'كود الطالب';
        }
        
        if (empty($row['student_name_ar'])) {
            $missingFields[] = 'اسم الطالب بالعربية';
        }
        
        if (empty($row['branch_name'])) {
            $missingFields[] = 'اسم الفرع';
        }
        
        if (empty($row['academic_band_name'])) {
            $missingFields[] = 'اسم الفرقة الأكاديمية';
        }
        
        if (empty($row['grade_class_name'])) {
            $missingFields[] = 'اسم الفصل الدراسي';
        }
        
        if (!empty($missingFields)) {
            return [
                'valid' => false,
                'message' => 'الحقول التالية مطلوبة ومفقودة: ' . implode(', ', $missingFields)
            ];
        }
        
        return ['valid' => true];
    }
    
    private function prepareStudentData(array $row)
    {
        
        // البحث عن الفرع والتأكد من وجوده
        $branchId = $this->findBranch($row['branch_name']);
        if (!$branchId) {
            throw new \Exception('الفرع المحدد غير موجود: ' . $row['branch_name']);
        }
        
        // البحث عن الفرقة الأكاديمية والتأكد من وجودها
        $academicBandId = $this->findAcademicBand($row['academic_band_name']);
        if (!$academicBandId) {
            throw new \Exception('الفرقة الأكاديمية المحددة غير موجودة: ' . $row['academic_band_name']);
        }
        
        // البحث عن الفصل والتأكد من وجوده
        $gradeClassId = $this->findGradeClass($row['grade_class_name'], $branchId, $academicBandId);
        if (!$gradeClassId) {
            throw new \Exception('الفصل الدراسي المحدد غير موجود: ' . $row['grade_class_name']);
        }
        
        // البحث عن الحافلة (اختياري)
        $busId = null;
        if (!empty($row['bus_code'])) {
            $busId = $this->findBus($row['bus_code']);
            if (!$busId) {
                $this->errors[] = 'تحذير: كود الحافلة غير موجود: ' . $row['bus_code'];
            }
        }
        
        // تحويل الجنس
        $gender = $this->normalizeGender($row['gender'] ?? 'ذكر');
        
        // تحويل حالة النشاط
        $isActive = $this->normalizeBoolean($row['is_active'] ?? 'نعم');
        
        return [
            'school_id' => $this->schoolId,
            'branch_id' => $branchId,
            'academic_band_id' => $academicBandId,
            'grade_class_id' => $gradeClassId,
            'bus_id' => $busId,
            'code' => $row['student_code'],
            'student_number' => $row['student_number'] ?? null,
            'name_ar' => $row['student_name_ar'],
            'name_en' => $row['student_name_en'] ?? null,
            'national_id' => $row['national_id'] ?? null,
            'date_of_birth' => $this->parseDate($row['date_of_birth'] ?? null),
            'gender' => $gender,
            'nationality' => $row['nationality'] ?? 'السعودية',
            'address_ar' => $row['address_ar'] ?? null,
            'address_en' => $row['address_en'] ?? null,
            'latitude' => is_numeric($row['latitude'] ?? null) ? (float) $row['latitude'] : null,
            'longitude' => is_numeric($row['longitude'] ?? null) ? (float) $row['longitude'] : null,
            'medical_notes' => $row['medical_notes'] ?? null,
            'emergency_contact' => $row['emergency_contact'] ?? null,
            'pickup_location' => $row['pickup_location'] ?? null,
            'is_active' => $isActive,
        ];
    }
    
    private function handleGuardians(Student $student, array $row)
    {
        // ولي الأمر الأول
        if (!empty($row['guardian_1_name'])) {
            $guardian1 = $this->createOrUpdateGuardian([
                'name_ar' => $row['guardian_1_name'],
                'phone' => $row['guardian_1_phone'] ?? null,
                'relationship' => $row['guardian_1_relationship'] ?? 'أب',
                'school_id' => $this->schoolId,
            ]);
            
            if ($guardian1) {
                $student->guardians()->syncWithoutDetaching([
                    $guardian1->id => ['is_primary' => true]
                ]);
            }
        }
        
        // ولي الأمر الثاني
        if (!empty($row['guardian_2_name'])) {
            $guardian2 = $this->createOrUpdateGuardian([
                'name_ar' => $row['guardian_2_name'],
                'phone' => $row['guardian_2_phone'] ?? null,
                'relationship' => $row['guardian_2_relationship'] ?? 'أم',
                'school_id' => $this->schoolId,
            ]);
            
            if ($guardian2) {
                $student->guardians()->syncWithoutDetaching([
                    $guardian2->id => ['is_primary' => false]
                ]);
            }
        }
    }
    
    private function createOrUpdateGuardian(array $guardianData)
    {
        if (empty($guardianData['name_ar'])) {
            return null;
        }
        
        return Guardian::updateOrCreate(
            [
                'name_ar' => $guardianData['name_ar'],
                'school_id' => $guardianData['school_id']
            ],
            $guardianData
        );
    }
    
    private function findBranch($branchName)
    {
        if (empty($branchName)) return $this->defaultBranchId;
        
        return Branch::where('school_id', $this->schoolId)
            ->where(function ($query) use ($branchName) {
                $query->where('name_ar', 'LIKE', '%' . $branchName . '%')
                      ->orWhere('name_en', 'LIKE', '%' . $branchName . '%')
                      ->orWhere('code', $branchName);
            })
            ->value('id');
    }
    
    private function findAcademicBand($bandName)
    {
        if (empty($bandName)) return null;
        
        return AcademicBand::where('school_id', $this->schoolId)
            ->where(function ($query) use ($bandName) {
                $query->where('name_ar', 'LIKE', '%' . $bandName . '%')
                      ->orWhere('name_en', 'LIKE', '%' . $bandName . '%');
            })
            ->value('id');
    }
    
    private function findGradeClass($className, $branchId = null, $academicBandId = null)
    {
        if (empty($className)) return null;
        
        $query = GradeClass::where('school_id', $this->schoolId);
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        if ($academicBandId) {
            $query->where('academic_band_id', $academicBandId);
        }
        
        return $query->where(function ($q) use ($className) {
                $q->where('name_ar', 'LIKE', '%' . $className . '%')
                  ->orWhere('name_en', 'LIKE', '%' . $className . '%')
                  ->orWhere('code', $className);
            })
            ->value('id');
    }
    
    private function findBus($busCode)
    {
        if (empty($busCode)) return null;
        
        return Bus::whereHas('branch', function ($query) {
                $query->where('school_id', $this->schoolId);
            })
            ->where('code', $busCode)
            ->value('id');
    }
    
    private function normalizeGender($gender)
    {
        $gender = strtolower(trim($gender));
        
        if (in_array($gender, ['أنثى', 'female', 'f', 'انثى', 'انثي'])) {
            return 'female';
        }
        
        return 'male'; // Default to male
    }
    
    private function normalizeBoolean($value)
    {
        $value = strtolower(trim($value));
        
        return in_array($value, ['نعم', 'yes', 'true', '1', 'active']);
    }
    
    private function parseDate($date)
    {
        if (empty($date)) return null;
        
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->errors[] = 'تنسيق التاريخ غير صحيح: ' . $date;
            return null;
        }
    }
    
    public function batchSize(): int
    {
        return 100;
    }
    
    public function chunkSize(): int
    {
        return 50;
    }
    
    public function rules(): array
    {
        return [
            'student_code' => 'required|string|max:50',
            'student_name_ar' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'academic_band_name' => 'required|string|max:255',
            'grade_class_name' => 'required|string|max:255',
            'student_number' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:ذكر,أنثى,male,female',
        ];
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
}
