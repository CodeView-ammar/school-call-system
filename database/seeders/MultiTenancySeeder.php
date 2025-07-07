<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MultiTenancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء مدرسة جديدة لاختبار Multi-tenancy
        $testSchool = \App\Models\School::create([
            'name_ar' => 'مدرسة النجاح الأهلية',
            'name_en' => 'Al-Najah Private School',
            'code' => 'NS001',
            'phone' => '0123456789',
            'email' => 'info@alnajah.edu.sa',
            'mobile' => '0512345678',
            'address_ar' => 'الرياض، المملكة العربية السعودية',
            'address_en' => 'Riyadh, Saudi Arabia',
            'student_capacity' => 500,
            'branch_count' => 2,
            'is_active' => true
        ]);

        // إنشاء مدير مدرسة للمدرسة الجديدة
        $schoolAdmin = \App\Models\User::create([
            'name' => 'Ahmad Al-Najjar',
            'name_ar' => 'أحمد النجار',
            'name_en' => 'Ahmad Al-Najjar',
            'email' => 'admin@alnajah.edu.sa',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'phone' => '0512345678',
            'is_active' => true,
            'school_id' => $testSchool->id,
            'user_type' => 'school_admin',
            'can_manage_school' => true,
            'school_permissions' => [
                'manage_users' => true,
                'manage_students' => true,
                'manage_branches' => true,
                'manage_buses' => true,
                'view_reports' => true,
                'manage_attendance' => true,
            ]
        ]);

        // إنشاء موظف في المدرسة
        $schoolStaff = \App\Models\User::create([
            'name' => 'Sarah Al-Mahmoud',
            'name_ar' => 'سارة المحمود',
            'name_en' => 'Sarah Al-Mahmoud',
            'email' => 'sarah@alnajah.edu.sa',
            'password' => \Illuminate\Support\Facades\Hash::make('staff123'),
            'phone' => '0512345679',
            'is_active' => true,
            'school_id' => $testSchool->id,
            'user_type' => 'staff',
            'can_manage_school' => false,
            'school_permissions' => [
                'manage_students' => true,
                'view_reports' => true,
                'manage_attendance' => true,
            ]
        ]);

        // تحديث المستخدم الأدمن الموجود ليكون سوبر أدمن
        \App\Models\User::where('email', 'admin@smartcall.com')->update([
            'user_type' => 'super_admin',
            'can_manage_school' => true,
            'school_id' => null, // السوبر أدمن لا يربط بمدرسة واحدة
            'school_permissions' => null
        ]);

        // تحديث المستخدم الثاني
        \App\Models\User::where('email', 'admin@admin.com')->update([
            'user_type' => 'super_admin',
            'can_manage_school' => true,
            'school_id' => null,
            'school_permissions' => null
        ]);

        $this->command->info('Multi-tenancy data created successfully!');
        $this->command->info('Test School Admin: admin@alnajah.edu.sa / admin123');
        $this->command->info('Test School Staff: sarah@alnajah.edu.sa / staff123');
    }
}
