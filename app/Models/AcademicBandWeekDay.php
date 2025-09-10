<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    // Boot method لإضافة قواعد عامة
    protected static function boot()
    {
        parent::boot();

        // قبل الحفظ، التحقق من عدم وجود تكرار
        static::saving(function ($model) {
            // التحقق من القيد الفريد المركب
            $existingRecord = static::where('school_id', $model->school_id)
                ->where('academic_band_id', $model->academic_band_id)
                ->where('week_day_id', $model->week_day_id)
                ->when($model->exists, function ($query) use ($model) {
                    // استثناء السجل الحالي عند التحديث
                    $query->where('id', '!=', $model->id);
                })
                ->first();

            if ($existingRecord) {
                // الحصول على تفاصيل التكرار
                $weekDay = WeekDay::where('school_id', $model->school_id)
                    ->where('day_id', $model->week_day_id)
                    ->first();
                
                $academicBand = AcademicBand::find($model->academic_band_id);
                
                $dayName = $weekDay ? $weekDay->day : 'هذا اليوم';
                $bandName = $academicBand ? $academicBand->name_ar : 'هذه الفرقة';
                
                // إرسال إشعار Filament بدلاً من Exception
                \Filament\Notifications\Notification::make()
                    ->title('❌ خطأ: يوجد تكرار في الجدول')
                    ->body("يوم {$dayName} مسجل مسبقاً للفرقة {$bandName}")
                    ->danger()
                    ->persistent()
                    ->send();
                
                // منع الحفظ بإرجاع false
                return false;
            }
            
            return true;
        });
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

    // دالة للتحقق من إمكانية إضافة جدول جديد
    public static function canAddSchedule($schoolId, $academicBandId, $weekDayId, $excludeId = null)
    {
        $query = static::where('school_id', $schoolId)
            ->where('academic_band_id', $academicBandId)
            ->where('week_day_id', $weekDayId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    // الحصول على الأيام المتاحة للفرقة
    public static function getAvailableDaysForBand($schoolId, $academicBandId)
    {
        $allDays = WeekDay::where('school_id', $schoolId)
            ->pluck('day_id')
            ->toArray();

        $usedDays = static::where('school_id', $schoolId)
            ->where('academic_band_id', $academicBandId)
            ->pluck('week_day_id')
            ->toArray();

        return array_diff($allDays, $usedDays);
    }

    // الحصول على الفرق النشطة في يوم معين
    public static function getActiveBandsForDay($schoolId, $weekDayId)
    {
        return static::forSchool($schoolId)
            ->forWeekDay($weekDayId)
            ->active()
            ->with(['academicBand', 'weekDay'])
            ->orderBy('start_time')
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
            ->orderBy('start_time')
            ->get();
    }

    // الحصول على الجدول الأسبوعي الكامل
    public static function getWeeklySchedule($schoolId, $academicBandId = null)
    {
        $query = static::forSchool($schoolId)
            ->active()
            ->with(['academicBand', 'weekDay']);

        if ($academicBandId) {
            $query->forAcademicBand($academicBandId);
        }

        return $query->orderBy('week_day_id')
            ->orderBy('start_time')
            ->get()
            ->groupBy('week_day_id');
    }

    // حساب إجمالي ساعات العمل للفرقة
    public function getDurationInHours()
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        return $end->diffInHours($start, true);
    }

    public function getDurationInMinutes()
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        return $end->diffInMinutes($start);
    }

    // الحصول على إجمالي ساعات العمل الأسبوعية للفرقة
    public static function getTotalWeeklyHoursForBand($schoolId, $academicBandId)
    {
        $schedules = static::forSchool($schoolId)
            ->forAcademicBand($academicBandId)
            ->active()
            ->get();

        $totalMinutes = 0;
        foreach ($schedules as $schedule) {
            $totalMinutes += $schedule->getDurationInMinutes();
        }

        return round($totalMinutes / 60, 2);
    }

    // Accessor للحصول على اسم اليوم
    public function getDayNameAttribute()
    {
        return $this->weekDay?->day ?? '';
    }

    // Accessor للحصول على اسم الفرقة
    public function getBandNameAttribute()
    {
        return $this->academicBand?->name_ar ?? '';
    }

    // Accessor للحصول على نطاق الوقت كنص
    public function getTimeRangeAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
        }
        return '';
    }

    // Accessor للحصول على المدة كنص
    public function getDurationTextAttribute()
    {
        $minutes = $this->getDurationInMinutes();
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return "{$hours} ساعة و {$remainingMinutes} دقيقة";
        } elseif ($hours > 0) {
            return "{$hours} ساعة";
        } else {
            return "{$remainingMinutes} دقيقة";
        }
    }
}