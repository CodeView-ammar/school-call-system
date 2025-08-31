<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekDay extends Model
{
    use HasFactory;

    protected $table = 'week_days';
    protected $primaryKey = 'day_id';

    protected $fillable = [
        'school_id',
        'branch_id',
        'day',
        'time_to',
        'time_from',
        'day_inactive',
        // 'branch_code',
        // 'customer_code',
        // 'band_id',
    ];

    protected $casts = [
        'time_to' => 'datetime:H:i:s',
        'time_from' => 'datetime:H:i:s',
        'day_inactive' => 'boolean',
    ];

    // العلاقات
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // العلاقة القديمة بالكود (للتوافق مع النظام القديم)
    public function branchByCode()
    {
        return $this->belongsTo(Branch::class, 'branch_code', 'code');
    }

    // نطاقات للاستعلام
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // public function scopeActive($query)
    // {
    //     return $query->where('day_inactive', true);
    // }

    // public function scopeInactive($query)
    // {
    //     return $query->where('day_inactive', false);
    // }

    // دالة للتحقق من تداخل الأوقات
    public function hasTimeConflict($day, $timeFrom, $timeTo, $excludeId = null)
    {
        $query = static::where('day', $day)
            ->where('school_id', $this->school_id)
            ->where('branch_id', $this->branch_id)
            ->where(function($q) use ($timeFrom, $timeTo) {
                $q->whereBetween('time_from', [$timeFrom, $timeTo])
                  ->orWhereBetween('time_to', [$timeFrom, $timeTo])
                  ->orWhere(function($q2) use ($timeFrom, $timeTo) {
                      $q2->where('time_from', '<=', $timeFrom)
                         ->where('time_to', '>=', $timeTo);
                  });
            });

        if ($excludeId) {
            $query->where('day_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    // دالة للحصول على أيام العمل
    public function scopeWorkingDays($query)
    {
        return $query->where('day_inactive', false);
    }

    // دالة للحصول على الأيام حسب الفرقة
    public function scopeForBand($query, $bandId)
    {
        return $query->where('band_id', $bandId);
    }

    public function academicBands()
    {
        return $this->belongsToMany(AcademicBand::class, 'academic_band_week_days', 'week_day_id', 'academic_band_id')
            ->withPivot(['school_id', 'start_time', 'end_time', 'is_active', 'notes'])
            ->withTimestamps();
    }

    public function academicBandWeekDays()
    {
        return $this->hasMany(AcademicBandWeekDay::class, 'week_day_id', 'day_id');
    }
}