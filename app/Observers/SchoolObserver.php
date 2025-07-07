<?php

namespace App\Observers;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;

class SchoolObserver
{
    /**
     * Handle the School "created" event.
     */
    public function created(School $school): void
    {
        // إنشاء مستخدم جديد للمدرسة
        $email = $this->generateSchoolEmail($school);
        $password = $this->generateRandomPassword();
        
        $user = User::create([
            'name' => $school->name_ar,
            'email' => $email,
            'password' => Hash::make($password),
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        
        // إعطاء دور مدير المدرسة
        $schoolAdminRole = \Spatie\Permission\Models\Role::where('name', 'school_admin')->first();
        if ($schoolAdminRole) {
            $user->assignRole('school_admin');
        }
        
        // إشعار المدير الأساسي
        Notification::make()
            ->title('تم إنشاء مستخدم جديد للمدرسة')
            ->body("تم إنشاء مستخدم جديد للمدرسة: {$school->name_ar}\nالبريد الإلكتروني: {$email}\nكلمة المرور: {$password}")
            ->success()
            ->persistent()
            ->send();
        
        // تسجيل بيانات المستخدم في سجل النظام
        \Log::info('تم إنشاء مستخدم جديد للمدرسة', [
            'school_id' => $school->id,
            'school_name' => $school->name_ar,
            'user_id' => $user->id,
            'user_email' => $email,
            'temporary_password' => $password,
        ]);
    }

    /**
     * Handle the School "updated" event.
     */
    public function updated(School $school): void
    {
        // تحديث اسم المستخدم الخاص بالمدرسة
        if ($school->isDirty('name_ar')) {
            $user = $school->admin;
            if ($user) {
                $user->update(['name' => $school->name_ar]);
            }
        }
    }

    /**
     * Handle the School "deleted" event.
     */
    public function deleted(School $school): void
    {
        // حذف المستخدم الخاص بالمدرسة
        $user = $school->admin;
        if ($user) {
            $user->delete();
        }
    }

    /**
     * إنشاء بريد إلكتروني للمدرسة
     */
    private function generateSchoolEmail(School $school): string
    {
        $baseEmail = Str::slug($school->name_en ?? $school->name_ar, '-');
        $domain = '@smartcall.school';
        
        $email = $baseEmail . $domain;
        
        // التأكد من عدم وجود البريد الإلكتروني مسبقاً
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = $baseEmail . $counter . $domain;
            $counter++;
        }
        
        return $email;
    }

    /**
     * إنشاء كلمة مرور عشوائية
     */
    private function generateRandomPassword(): string
    {
        return Str::random(12);
    }
}