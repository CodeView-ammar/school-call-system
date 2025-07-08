<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicBandWeekDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_band_id',
        'week_day_id',
        'start_time',
        'end_time',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicBand()
    {
        return $this->belongsTo(AcademicBand::class);
    }

    public function weekDay()
    {
        return $this->belongsTo(WeekDay::class, 'week_day_id', 'day_id');
    }

    // نطاقات الاستعلام
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForAcademicBand($query, $bandId)
    {
        return $query->where('academic_band_id', $bandId);
    }

    public function scopeForWeekDay($query, $dayId)
    {
        return $query->where('week_day_id', $dayId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // دالة للتحقق من تداخل الأوقات للفرقة نفسها
    public function hasTimeConflict($startTime, $endTime, $excludeId = null)
    {
        $query = static::where('academic_band_id', $this->academic_band_id)
            ->where('week_day_id', $this->week_day_id)
            ->where('is_active', true)
            ->where(function($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    // الحصول على الفرق النشطة في يوم معين
    public static function getActiveBandsForDay($schoolId, $weekDayId)
    {
        return static::forSchool($schoolId)
            ->forWeekDay($weekDayId)
            ->active()
            ->with(['academicBand', 'weekDay'])
            ->get();
    }

    // الحصول على أيام العمل للفرقة
    public static function getWorkingDaysForBand($schoolId, $bandId)
    {
        return static::forSchool($schoolId)
            ->forAcademicBand($bandId)
            ->active()
            ->with(['weekDay', 'academicBand'])
            ->orderBy('week_day_id')
            ->get();
    }
}
