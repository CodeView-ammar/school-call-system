<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Branch;
use App\Models\EducationLevel;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\Supervisor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
// use Spatie\Permission\Models\Permission;

class ComprehensiveSeeder extends Seeder
{
    public function run(): void
    {
        // تشغيل seeders الأساسية أولاً
        $this->call([
            RolesAndPermissionsSeeder::class,
            EducationLevelSeeder::class,
            CallTypesSeeder::class,
            HolidaysSeeder::class,
        ]);

        // إنشاء المستخدم المدير الرئيسي
        $this->createSuperAdmin();

        // إنشاء المدرسة الافتراضية
        $school = $this->createDefaultSchool();

        // إنشاء الفروع
        $branches = $this->createBranches($school);

        // إنشاء الصفوف والمراحل
        $grades = $this->createGradesAndClasses($school);

        // إنشاء الطلاب
        $students = $this->createStudents($school, $branches[0], $grades);

        // إنشاء أولياء الأمور
        $this->createGuardians($students);

        // إنشاء الحافلات والسائقين والمشرفين
        $this->createTransportation($school, $branches[0]);

        // إنشاء مستخدمين إضافيين
        $this->createAdditionalUsers($school);
    }

    private function createSuperAdmin()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@smartcall.com'],
            [
                'name' => 'مدير النظام الرئيسي',
                'name_ar' => 'مدير النظام الرئيسي',
                'name_en' => 'Super Administrator',
                'email' => 'admin@smartcall.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $admin->assignRole($superAdminRole);
        }

        $this->command->info('✅ تم إنشاء مستخدم المدير الرئيسي: admin@smartcall.com / password123');
    }

    private function createDefaultSchool()
    {
        $school = School::firstOrCreate(
            ['name_ar' => 'مدرسة الأمل النموذجية'],
            [
                'name_ar' => 'مدرسة الأمل النموذجية',
                'name_en' => 'Al-Amal Model School',
                'code' => 'SCH-001',
                'address_ar' => 'الرياض، حي النرجس، شارع الملك فهد',
                'address_en' => 'Riyadh, Al-Narges District, King Fahd Street',
                'phone' => '0114567890',
                'email' => 'info@alamal-school.edu.sa',
                'student_capacity' => 1000,
                'branch_count' => 2,
                'is_active' => true,
            ]
        );

        $this->command->info('✅ تم إنشاء المدرسة الافتراضية: ' . $school->name_ar);
        return $school;
    }

    private function createBranches($school)
    {
        $branches = [];

        $branchesData = [
            [
                'name_ar' => 'الفرع الرئيسي',
                'name_en' => 'Main Branch',
                'address_ar' => 'الرياض، حي النرجس الرئيسي',
                'address_en' => 'Riyadh, Main Al-Narges',
                'phone' => '0114567891',
                'manager_name' => 'أحمد محمد العلي',
                'is_main' => true,
            ],
            [
                'name_ar' => 'فرع البنات',
                'name_en' => 'Girls Branch',
                'address_ar' => 'الرياض، حي النرجس - قسم البنات',
                'address_en' => 'Riyadh, Al-Narges - Girls Section',
                'phone' => '0114567892',
                'manager_name' => 'فاطمة سعد الأحمد',
                'is_main' => false,
            ]
        ];

        foreach ($branchesData as $branchData) {
            $branch = Branch::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'name_ar' => $branchData['name_ar']
                ],
                array_merge($branchData, [
                    'school_id' => $school->id,
                    'is_active' => true,
                ])
            );
            $branches[] = $branch;
        }

        $this->command->info('✅ تم إنشاء ' . count($branches) . ' فروع للمدرسة');
        return $branches;
    }

    private function createGradesAndClasses($school)
    {
        $grades = [];
        $educationLevels = EducationLevel::all();

        $gradesData = [
            // المرحلة الابتدائية
            ['name_ar' => 'الصف الأول الابتدائي', 'name_en' => 'Grade 1', 'level_name' => 'ابتدائي'],
            ['name_ar' => 'الصف الثاني الابتدائي', 'name_en' => 'Grade 2', 'level_name' => 'ابتدائي'],
            ['name_ar' => 'الصف الثالث الابتدائي', 'name_en' => 'Grade 3', 'level_name' => 'ابتدائي'],
            ['name_ar' => 'الصف الرابع الابتدائي', 'name_en' => 'Grade 4', 'level_name' => 'ابتدائي'],
            ['name_ar' => 'الصف الخامس الابتدائي', 'name_en' => 'Grade 5', 'level_name' => 'ابتدائي'],
            ['name_ar' => 'الصف السادس الابتدائي', 'name_en' => 'Grade 6', 'level_name' => 'ابتدائي'],
            // المرحلة المتوسطة
            ['name_ar' => 'الصف الأول المتوسط', 'name_en' => 'Grade 7', 'level_name' => 'متوسط'],
            ['name_ar' => 'الصف الثاني المتوسط', 'name_en' => 'Grade 8', 'level_name' => 'متوسط'],
            ['name_ar' => 'الصف الثالث المتوسط', 'name_en' => 'Grade 9', 'level_name' => 'متوسط'],
        ];

        foreach ($gradesData as $gradeData) {
            $educationLevel = $educationLevels->where('name_ar', $gradeData['level_name'])->first();
            if (!$educationLevel) continue;

            $grade = Grade::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'name_ar' => $gradeData['name_ar']
                ],
                [
                    'school_id' => $school->id,
                    'education_level_id' => $educationLevel->id,
                    'name_ar' => $gradeData['name_ar'],
                    'name_en' => $gradeData['name_en'],
                    'is_active' => true,
                ]
            );

            // إنشاء فصول لكل صف
            $classes = ['أ', 'ب', 'ج'];
            foreach ($classes as $className) {
                SchoolClass::firstOrCreate(
                    [
                        'school_id' => $school->id,
                        'grade_id' => $grade->id,
                        'name_ar' => $gradeData['name_ar'] . ' - فصل ' . $className
                    ],
                    [
                        'school_id' => $school->id,
                        'grade_id' => $grade->id,
                        'name_ar' => $gradeData['name_ar'] . ' - فصل ' . $className,
                        'name_en' => $gradeData['name_en'] . ' - Class ' . $className,
                        'capacity' => 30,
                        'current_count' => 0,
                        'is_active' => true,
                    ]
                );
            }

            $grades[] = $grade;
        }

        $this->command->info('✅ تم إنشاء ' . count($grades) . ' صفوف دراسية مع فصولها');
        return $grades;
    }

    private function createStudents($school, $branch, $grades)
    {
        $students = [];
        $arabicNames = [
            'males' => [
                'محمد أحمد العلي', 'عبدالله سعد المحمد', 'خالد فهد الأحمد', 'سعد محمد الخالد',
                'عمر عبدالله السعد', 'حسام أحمد الفهد', 'طارق محمد العمر', 'ياسر سعد الحسام',
                'نواف خالد الطارق', 'فيصل عمر الياسر', 'بندر حسام النواف', 'مشعل طارق الفيصل',
                'راكان ياسر البندر', 'تركي نواف المشعل', 'عادل فيصل الراكان', 'وليد بندر التركي',
                'صالح مشعل العادل', 'ماجد راكان الوليد', 'حمد تركي الصالح', 'عبدالرحمن عادل الماجد'
            ],
            'females' => [
                'فاطمة أحمد العلي', 'عائشة سعد المحمد', 'مريم فهد الأحمد', 'زينب محمد الخالد',
                'خديجة عبدالله السعد', 'أم كلثوم أحمد الفهد', 'سارة محمد العمر', 'نورا سعد الحسام',
                'لينا خالد الطارق', 'رنا عمر الياسر', 'دانا حسام النواف', 'جنى طارق الفيصل',
                'ريم ياسر البندر', 'لمى نواف المشعل', 'شهد فيصل الراكان', 'نور بندر التركي',
                'ملك مشعل العادل', 'جود راكان الوليد', 'غلا تركي الصالح', 'روان عادل الماجد'
            ]
        ];

        $studentCounter = 1;

        foreach ($grades as $grade) {
            $classes = SchoolClass::where('grade_id', $grade->id)->get();
            
            foreach ($classes as $class) {
                // إنشاء 15-25 طالب لكل فصل
                $studentsCount = rand(15, 25);
                
                for ($i = 0; $i < $studentsCount; $i++) {
                    $gender = rand(0, 1) ? 'male' : 'female';
                    $namesList = $arabicNames[$gender . 's'];
                    $randomName = $namesList[array_rand($namesList)];
                    
                    $studentCode = 'STD-' . str_pad($studentCounter, 4, '0', STR_PAD_LEFT);
                    
                    $student = Student::firstOrCreate(
                        ['code' => $studentCode],
                        [
                            'school_id' => $school->id,
                            'branch_id' => $branch->id,
                            'school_class_id' => $class->id,
                            'code' => $studentCode,
                            'student_number' => $studentCounter,
                            'name_ar' => $randomName,
                            'name_en' => $this->translateToEnglish($randomName),
                            'gender' => $gender,
                            'nationality' => 'سعودي',
                            'date_of_birth' => now()->subYears(rand(6, 16))->subDays(rand(1, 365)),
                            'national_id' => '1' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                            'place_of_birth' => 'الرياض',
                            'address_ar' => 'الرياض، حي ' . ['النرجس', 'العليا', 'الملز', 'الروضة'][rand(0, 3)],
                            'phone' => '05' . rand(10000000, 99999999),
                            'emergency_contact' => '05' . rand(10000000, 99999999),
                            'medical_notes' => rand(0, 5) == 0 ? 'حساسية من ' . ['الفول السوداني', 'اللاكتوز', 'البيض'][rand(0, 2)] : null,
                            'enrollment_date' => now()->subMonths(rand(1, 24)),
                            'is_active' => true,
                        ]
                    );
                    
                    $students[] = $student;
                    $studentCounter++;
                }
                
                // تحديث عدد الطلاب في الفصل
                $class->update(['current_count' => $studentsCount]);
            }
        }

        $this->command->info('✅ تم إنشاء ' . count($students) . ' طالب');
        return $students;
    }

    private function createGuardians($students)
    {
        $guardians = [];
        $guardianNames = [
            'males' => [
                'أحمد محمد العلي', 'سعد عبدالله المحمد', 'فهد خالد الأحمد', 'محمد سعد الخالد',
                'عبدالله أحمد السعد', 'حسام فهد الأحمد', 'طارق محمد العمر', 'ياسر سعد الحسام'
            ],
            'females' => [
                'فاطمة أحمد العلي', 'عائشة سعد المحمد', 'مريم فهد الأحمد', 'زينب محمد الخالد',
                'خديجة عبدالله السعد', 'أم كلثوم أحمد الفهد', 'سارة محمد العمر', 'نورا سعد الحسام'
            ]
        ];

        $relationships = ['أب', 'أم', 'جد', 'جدة', 'عم', 'عمة', 'خال', 'خالة'];
        $jobs = ['مهندس', 'طبيب', 'معلم', 'محاسب', 'موظف حكومي', 'تاجر', 'مقاول', 'صيدلي'];

        $processedFamilies = [];
        
        foreach ($students as $student) {
            // استخدام اسم العائلة لتجميع الأشقاء
            $familyName = explode(' ', $student->name_ar)[2] ?? 'العائلة';
            
            if (!isset($processedFamilies[$familyName])) {
                // إنشاء الأب
                $father = Guardian::create([
                    'name_ar' => $guardianNames['males'][array_rand($guardianNames['males'])],
                    'name_en' => 'Father Name',
                    'relationship' => 'أب',
                    'phone' => '05' . rand(10000000, 99999999),
                    'phone_2' => rand(0, 1) ? '05' . rand(10000000, 99999999) : null,
                    'email' => 'father.' . strtolower(str_replace(' ', '.', $familyName)) . '@email.com',
                    'national_id' => '1' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                    'job' => $jobs[array_rand($jobs)],
                    'workplace' => 'شركة ' . ['الاتصالات', 'البترول', 'الكهرباء', 'المياه'][rand(0, 3)],
                    'address_ar' => 'الرياض، حي ' . ['النرجس', 'العليا', 'الملز', 'الروضة'][rand(0, 3)],
                    'is_active' => true,
                ]);

                // إنشاء الأم
                $mother = Guardian::create([
                    'name_ar' => $guardianNames['females'][array_rand($guardianNames['females'])],
                    'name_en' => 'Mother Name',
                    'relationship' => 'أم',
                    'phone' => '05' . rand(10000000, 99999999),
                    'email' => 'mother.' . strtolower(str_replace(' ', '.', $familyName)) . '@email.com',
                    'national_id' => '2' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                    'job' => rand(0, 1) ? $jobs[array_rand($jobs)] : 'ربة منزل',
                    'address_ar' => 'الرياض، حي ' . ['النرجس', 'العليا', 'الملز', 'الروضة'][rand(0, 3)],
                    'is_active' => true,
                ]);

                $processedFamilies[$familyName] = [$father, $mother];
                $guardians = array_merge($guardians, [$father, $mother]);
            }

            // ربط الطالب بالوالدين
            $familyGuardians = $processedFamilies[$familyName];
            $student->guardians()->attach($familyGuardians[0]->id, ['is_primary' => true]); // الأب رئيسي
            $student->guardians()->attach($familyGuardians[1]->id, ['is_primary' => false]); // الأم ثانوية
        }

        $this->command->info('✅ تم إنشاء ' . count($guardians) . ' ولي أمر وربطهم بالطلاب');
        return $guardians;
    }

    private function createTransportation($school, $branch)
    {
        // إنشاء السائقين
        $drivers = [];
        $driverNames = [
            'محمد أحمد الناقل', 'سعد فهد المسافر', 'خالد عبدالله الراكب',
            'عمر محمد السائق', 'حسام سعد الطريق'
        ];

        foreach ($driverNames as $name) {
            $driver = Driver::create([
                'school_id' => $school->id,
                'name_ar' => $name,
                'name_en' => 'Driver Name',
                'phone' => '05' . rand(10000000, 99999999),
                'license_number' => rand(100000000, 999999999),
                'license_expiry' => now()->addYears(2),
                'national_id' => '1' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'hire_date' => now()->subMonths(rand(6, 24)),
                'is_active' => true,
            ]);
            $drivers[] = $driver;
        }

        // إنشاء المشرفين
        $supervisors = [];
        $supervisorNames = [
            'فاطمة أحمد المشرفة', 'عائشة سعد الراقبة', 'مريم فهد المتابعة'
        ];

        foreach ($supervisorNames as $name) {
            $supervisor = Supervisor::create([
                'school_id' => $school->id,
                'name_ar' => $name,
                'name_en' => 'Supervisor Name',
                'phone' => '05' . rand(10000000, 99999999),
                'national_id' => '2' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'hire_date' => now()->subMonths(rand(6, 24)),
                'is_active' => true,
            ]);
            $supervisors[] = $supervisor;
        }

        // إنشاء الحافلات
        $buses = [];
        for ($i = 1; $i <= 5; $i++) {
            $bus = Bus::create([
                'school_id' => $school->id,
                'branch_id' => $branch->id,
                'driver_id' => $drivers[($i-1) % count($drivers)]->id,
                'supervisor_id' => $supervisors[($i-1) % count($supervisors)]->id,
                'bus_number' => 'BUS-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'plate_number' => 'ر ق م ' . rand(1000, 9999),
                'model' => ['مرسيدس', 'فولفو', 'سكانيا', 'إيفيكو'][rand(0, 3)],
                'year' => rand(2018, 2024),
                'capacity' => rand(30, 50),
                'insurance_expiry' => now()->addYear(),
                'license_expiry' => now()->addMonths(6),
                'last_maintenance' => now()->subWeeks(rand(1, 8)),
                'next_maintenance' => now()->addWeeks(rand(4, 12)),
                'is_active' => true,
            ]);
            $buses[] = $bus;
        }

        $this->command->info('✅ تم إنشاء ' . count($drivers) . ' سائق، ' . count($supervisors) . ' مشرف، ' . count($buses) . ' حافلة');
    }

    private function createAdditionalUsers($school)
    {
        // مدير المدرسة
        $schoolAdmin = User::firstOrCreate(
            ['email' => 'school.admin@smartcall.com'],
            [
                'name' => 'مدير المدرسة',
                'name_ar' => 'أحمد محمد مدير المدرسة',
                'name_en' => 'Ahmad Mohammad School Admin',
                'email' => 'school.admin@smartcall.com',
                'password' => Hash::make('password123'),
                'school_id' => $school->id,
                'is_active' => true,
            ]
        );

        // معلم
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@smartcall.com'],
            [
                'name' => 'معلم النظام',
                'name_ar' => 'محمد سعد المعلم',
                'name_en' => 'Mohammad Saad Teacher',
                'email' => 'teacher@smartcall.com',
                'password' => Hash::make('password123'),
                'school_id' => $school->id,
                'is_active' => true,
            ]
        );

        // تعيين الأدوار
        $schoolAdminRole = Role::where('name', 'school_admin')->first();
        $teacherRole = Role::where('name', 'teacher')->first();

        if ($schoolAdminRole) $schoolAdmin->assignRole($schoolAdminRole);
        if ($teacherRole) $teacher->assignRole($teacherRole);

        $this->command->info('✅ تم إنشاء مستخدمين إضافيين للمدرسة');
    }

    private function translateToEnglish($arabicName)
    {
        $translations = [
            'محمد' => 'Mohammad', 'أحمد' => 'Ahmad', 'عبدالله' => 'Abdullah',
            'خالد' => 'Khalid', 'سعد' => 'Saad', 'فهد' => 'Fahad',
            'فاطمة' => 'Fatima', 'عائشة' => 'Aisha', 'مريم' => 'Maryam',
            'زينب' => 'Zainab', 'خديجة' => 'Khadija', 'العلي' => 'Al-Ali',
            'المحمد' => 'Al-Mohammad', 'الأحمد' => 'Al-Ahmad'
        ];

        $words = explode(' ', $arabicName);
        $englishWords = [];

        foreach ($words as $word) {
            $englishWords[] = $translations[$word] ?? $word;
        }

        return implode(' ', $englishWords);
    }
}