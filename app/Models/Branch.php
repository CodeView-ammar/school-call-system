<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions;
use Exception;
use Filament\Notifications\Notification;

class Branch extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'school_id',
        'name_ar',
        'name_en', 
        'code',
        'logo',
        'address_ar',
        'address_en',
        'latitude',
        'longitude',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($branch) {
            $branch->school?->updateBranchCount();
        });

        static::updated(function ($branch) {
            if ($branch->isDirty('school_id')) {
                // إذا تغيرت تبعية الفرع لمدرسة أخرى
                $originalSchool = School::find($branch->getOriginal('school_id'));
                $originalSchool?->updateBranchCount();

                $branch->school?->updateBranchCount();
            } else {
                $branch->school?->updateBranchCount();
            }
        });

        static::deleted(function ($branch) {
            
            $branch->school?->updateBranchCount();
            
        });
        static::deleting(function ($branch) {
            if (!$branch->school?->branch_settings['allow_branch_deletion']) {
                Notification::make()
                ->title('خطأ')
                ->body("لا تستطيع حذف الفرع لا تمتلك صلاحية")
                ->danger() // تجعل الرسالة باللون الأحمر وتُعتبر رسالة خطأ
                ->send();
                return false;
                
                // throw new \Exception('هذه المدرسة لا تسمح بحذف الفروع.');
            }
        });
    }

    public function allowBranchDeletion(): bool
    {
        return $this->school?->allow_branch_deletion ?? false;
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function earlyArrivals()
    {
        return $this->hasMany(EarlyArrival::class);
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function weekDays()
    {
        return $this->hasMany(WeekDay::class);
    }

    public function gradeClass()
    {
        return $this->hasMany(GradeClass::class);
    }

    
    public function buses()
    {
        return $this->hasMany(Bus::class);
    }
    
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?? $this->name_en;
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
    

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }
}
