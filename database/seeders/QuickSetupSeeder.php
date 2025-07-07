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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class QuickSetupSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء المستخدم المدير الرئيسي
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

        // إنشاء المدرسة الافتراضية
        $school = School::firstOrCreate(
            ['name_ar' => 'مدرسة الأمل النموذجية'],
            [
                'name_ar' => 'مدرسة الأمل النموذجية',
                'name_en' => 'Al-Amal Model School',
                'code' => 'SCH-001',
                'phone' => '0114567890',
                'email' => 'info@alamal-school.edu.sa',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ تم إنشاء المدرسة الافتراضية: ' . $school->name_ar);

        // إنشاء فرع افتراضي
        $branch = Branch::firstOrCreate(
            ['school_id' => $school->id, 'code' => 'MAIN'],
            [
                'school_id' => $school->id,
                'name_ar' => 'الفرع الرئيسي',
                'name_en' => 'Main Branch',
                'code' => 'MAIN',
                'address_ar' => 'الرياض، الفرع الرئيسي',
                'is_active' => true,
            ]
        );

        // إنشاء صفوف تجريبية
        $educationLevel = EducationLevel::where('name_ar', 'ابتدائي')->first();
        if ($educationLevel) {
            $grade = Grade::firstOrCreate(
                ['school_id' => $school->id, 'name_ar' => 'الصف الأول الابتدائي'],
                [
                    'school_id' => $school->id,
                    'education_level_id' => $educationLevel->id,
                    'name_ar' => 'الصف الأول الابتدائي',
                    'name_en' => 'Grade 1',
                    'is_active' => true,
                ]
            );

            $class = SchoolClass::firstOrCreate(
                ['school_id' => $school->id, 'grade_id' => $grade->id, 'name_ar' => 'الصف الأول الابتدائي - فصل أ'],
                [
                    'school_id' => $school->id,
                    'grade_id' => $grade->id,
                    'name_ar' => 'الصف الأول الابتدائي - فصل أ',
                    'name_en' => 'Grade 1 - Class A',
                    'capacity' => 30,
                    'current_count' => 0,
                    'is_active' => true,
                ]
            );

            // إنشاء طلاب تجريبيين
            $students = [];
            $studentNames = [
                ['محمد أحمد العلي', 'male'],
                ['فاطمة سعد المحمد', 'female'],
                ['عبدالله خالد الأحمد', 'male'],
                ['نورا فهد الخالد', 'female'],
                ['سعد محمد السعد', 'male'],
            ];

            foreach ($studentNames as $index => $nameData) {
                $student = Student::firstOrCreate(
                    ['code' => 'STD-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                    [
                        'school_id' => $school->id,
                        'branch_id' => $branch->id,
                        'school_class_id' => $class->id,
                        'code' => 'STD-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                        'student_number' => $index + 1,
                        'name_ar' => $nameData[0],
                        'name_en' => 'Student Name',
                        'gender' => $nameData[1],
                        'nationality' => 'سعودي',
                        'date_of_birth' => now()->subYears(7)->subDays(rand(1, 365)),
                        'is_active' => true,
                    ]
                );
                $students[] = $student;
            }

            // إنشاء أولياء أمور تجريبيين
            $guardianNames = [
                ['أحمد محمد العلي', 'أب'],
                ['مريم سعد العلي', 'أم'],
                ['سعد فهد المحمد', 'أب'],
                ['خديجة أحمد المحمد', 'أم'],
            ];

            foreach ($guardianNames as $index => $guardianData) {
                $guardian = Guardian::firstOrCreate(
                    ['phone' => '05' . str_pad($index + 1, 8, '0', STR_PAD_LEFT)],
                    [
                        'name_ar' => $guardianData[0],
                        'name_en' => 'Guardian Name',
                        'relationship' => $guardianData[1],
                        'phone' => '05' . str_pad($index + 1, 8, '0', STR_PAD_LEFT),
                        'email' => 'guardian' . ($index + 1) . '@email.com',
                        'is_active' => true,
                    ]
                );

                // ربط ولي الأمر بالطالب
                if (isset($students[$index])) {
                    $guardian->students()->syncWithoutDetaching([
                        $students[$index]->id => ['is_primary' => $guardianData[1] === 'أب']
                    ]);
                }
            }

            $this->command->info('✅ تم إنشاء ' . count($students) . ' طلاب تجريبيين');
            $this->command->info('✅ تم إنشاء ' . count($guardianNames) . ' أولياء أمور تجريبيين');
        }

        $this->command->info('🎉 تم إعداد النظام بنجاح!');
        $this->command->info('بيانات الدخول: admin@smartcall.com / password123');
    }
}