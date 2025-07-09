<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminOnlySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // مسح cache الصلاحيات
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الصلاحيات الأساسية
        $permissions = [
            // صلاحيات المدارس (للمدير الأساسي فقط)
            'manage_schools',
            'create_schools',
            'edit_schools',
            'delete_schools',
            'view_schools',
            
            // صلاحيات المستخدمين (للمدير الأساسي فقط)
            'manage_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_users',
            
            // صلاحيات الأدوار والصلاحيات (للمدير الأساسي فقط)
            'manage_roles',
            'manage_permissions',
            
            // صلاحيات خاصة بالمدرسة (لمدير المدرسة)
            'manage_school_data',
            'view_school_data',
            'manage_branches',
            'manage_students',
            'manage_teachers',
            'manage_buses',
            'manage_attendance',
            'view_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // إنشاء الأدوار
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $schoolAdminRole = Role::firstOrCreate(['name' => 'school_admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

        // إعطاء جميع الصلاحيات للمدير الأساسي
        $superAdminRole->givePermissionTo(Permission::all());

        // إعطاء صلاحيات المدرسة لمدير المدرسة
        $schoolAdminRole->givePermissionTo([
            'manage_school_data',
            'view_school_data',
            'manage_branches',
            'manage_students',
            'manage_teachers',
            'manage_buses',
            'manage_attendance',
            'view_reports',
        ]);

        // إعطاء صلاحيات محدودة للمعلم
        $teacherRole->givePermissionTo([
            'view_school_data',
            'manage_students',
            'manage_attendance',
            'view_reports',
        ]);

        // إعطاء صلاحيات محدودة للمشرف
        $supervisorRole->givePermissionTo([
            'view_school_data',
            'manage_buses',
            'manage_attendance',
            'view_reports',
        ]);

        // تحديث المستخدم الأساسي ليكون super_admin
        $adminUser = User::where('email', 'admin@smartcall.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('super_admin');
        }
    }
}