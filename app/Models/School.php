<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\Permission;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en', 
        'code',
        'vat_no',
        'trade_registration_no',
        'phone',
        'email',
        'mobile',
        'logo',
        'address_ar',
        'address_en',
        'latitude',
        'longitude',
        'student_capacity',
        'branch_count',
        'is_active',
        'max_branches',
        'current_branches_count',
        'allow_unlimited_branches',
        'branch_settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'student_capacity' => 'integer',
        'branch_count' => 'integer',
        'max_branches' => 'integer',
        'current_branches_count' => 'integer',
        'allow_unlimited_branches' => 'boolean',
        'branch_settings' => 'array',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
    public function earlyArrivals()
    {
        return $this->hasMany(EarlyArrival::class);
    }
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?? $this->name_en;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // المستخدمين المرتبطين بالمدرسة
    public function users()
    {
        return $this->hasMany(User::class);
    }

     // المستخدمين المرتبطين بالمدرسة
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    // الصلاحيات المرتبطة بالمدرسة
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    // مدير المدرسة الرئيسي
    public function schoolAdmin()
    {
        return $this->hasOne(User::class)->where('user_type', 'school_admin')->where('can_manage_school', true);
    }

    // جميع الموظفين في المدرسة
    public function staff()
    {
        return $this->hasMany(User::class)->where('user_type', 'staff');
    }

    // دوال إدارة الفروع
    public function canAddMoreBranches(): bool
    {
        if ($this->allow_unlimited_branches) {
            return true;
        }

        return $this->current_branches_count < $this->max_branches;
    }

    public function getRemainingBranchesAttribute(): int
    {
        if ($this->allow_unlimited_branches) {
            return -1; // غير محدود
        }

        return max(0, $this->max_branches - $this->current_branches_count);
    }

    public function updateBranchCount(): void
    {
        $this->current_branches_count = $this->branches()->count();
        $this->save();
    }

    public function getBranchSettingsAttribute($value): array
    {
        return $value ? json_decode($value, true) : [
            'allow_branch_management' => true,
            'require_approval_for_new_branches' => false,
            'max_students_per_branch' => 500,
            'allow_branch_deletion' => false
        ];
    }

    public function setBranchSettingsAttribute($value): void
    {
        $this->attributes['branch_settings'] = json_encode($value);
    }
    public function weekDays()
    {
        return $this->hasMany(WeekDay::class);
    }

    public function callTypes()
    {
        return $this->hasMany(CallType::class, 'school_id');
    }

    /**
     * العلاقة مع إعدادات النظام
     */
    public function systemSetting()
    {
        return $this->hasOne(SystemSetting::class);
    }

    public function academicBands()
    {
        return $this->hasMany(AcademicBand::class);
    }

    public function academicBandWeekDays()
    {
        return $this->hasMany(AcademicBandWeekDay::class);
    }

    public function busRoutes()
    {
        return $this->hasMany(BusRoute::class);
    }

    public function guardians()
    {
        return $this->hasMany(Guardian::class);
    }
    public function gates()
    {
        return $this->hasMany(Gate::class);
    }
    public function gradeClasses()
{
    return $this->hasMany(GradeClass::class);
}
}