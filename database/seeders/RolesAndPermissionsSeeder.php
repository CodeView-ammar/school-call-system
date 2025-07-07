<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // إعادة تعيين الأدوار والصلاحيات المخزنة مؤقتاً
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الصلاحيات
        $permissions = [
            // صلاحيات النظام العامة
            'view_dashboard',
            'manage_system',
            
            // صلاحيات إدارة المدارس
            'view_schools',
            'create_schools',
            'edit_schools',
            'delete_schools',
            
            // صلاحيات إدارة الفروع
            'view_branches',
            'create_branches',
            'edit_branches',
            'delete_branches',
            
            // صلاحيات إدارة الطلاب
            'view_students',
            'create_students',
            'edit_students',
            'delete_students',
            'view_all_students', // للادمن العام
            
            // صلاحيات إدارة أولياء الأمور
            'view_guardians',
            'create_guardians',
            'edit_guardians',
            'delete_guardians',
            
            // صلاحيات إدارة الحافلات
            'view_buses',
            'create_buses',
            'edit_buses',
            'delete_buses',
            'view_all_buses', // للادمن العام
            
            // صلاحيات إدارة السائقين
            'view_drivers',
            'create_drivers',
            'edit_drivers',
            'delete_drivers',
            
            // صلاحيات إدارة المشرفين
            'view_supervisors',
            'create_supervisors',
            'edit_supervisors',
            'delete_supervisors',
            
            // صلاحيات النداء والحضور
            'view_call_sessions',
            'create_call_sessions',
            'edit_call_sessions',
            'delete_call_sessions',
            'manage_attendance',
            
            // صلاحيات التقارير
            'view_reports',
            'export_reports',
            'view_analytics',
            
            // صلاحيات إدارة المستخدمين
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'assign_roles'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // إنشاء الأدوار
        
        // 1. الأدمن العام - يستطيع الوصول لجميع البيانات
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. أدمن المدرسة - يدير مدرسة واحدة فقط
        $schoolAdmin = Role::create(['name' => 'school-admin']);
        $schoolAdmin->givePermissionTo([
            'view_dashboard',
            'view_schools',
            'edit_schools',
            'view_branches',
            'create_branches',
            'edit_branches',
            'view_students',
            'create_students',
            'edit_students',
            'view_guardians',
            'create_guardians',
            'edit_guardians',
            'view_buses',
            'create_buses',
            'edit_buses',
            'view_drivers',
            'create_drivers',
            'edit_drivers',
            'view_supervisors',
            'create_supervisors',
            'edit_supervisors',
            'view_call_sessions',
            'create_call_sessions',
            'edit_call_sessions',
            'view_reports',
            'export_reports',
            'view_users',
            'create_users',
            'edit_users'
        ]);

        // 3. المشرف - يدير حافلات محددة فقط
        $supervisor = Role::create(['name' => 'supervisor']);
        $supervisor->givePermissionTo([
            'view_dashboard',
            'view_students',
            'view_buses',
            'view_call_sessions',
            'create_call_sessions',
            'edit_call_sessions',
            'manage_attendance',
            'view_reports'
        ]);

        // 4. السائق - يرى بياناته فقط
        $driver = Role::create(['name' => 'driver']);
        $driver->givePermissionTo([
            'view_dashboard',
            'view_students',
            'view_buses',
            'view_call_sessions'
        ]);

        // 5. المعلم - يرى طلاب صفه فقط
        $teacher = Role::create(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'view_dashboard',
            'view_students',
            'manage_attendance',
            'view_reports'
        ]);

        // إنشاء المستخدم الأدمن العام
        $adminUser = User::create([
            'name' => 'مدير النظام',
            'name_ar' => 'مدير النظام',
            'name_en' => 'System Administrator',
            'email' => 'admin@smartcall.com',
            'password' => bcrypt('admin123'),
            'phone' => '966501234567',
            'is_active' => true,
            'email_verified_at' => now()
        ]);

        $adminUser->assignRole('super-admin');

        $this->command->info('تم إنشاء الأدوار والصلاحيات والمستخدم الأدمن بنجاح');
        $this->command->info('بيانات الدخول: admin@smartcall.com / admin123');
    }
}