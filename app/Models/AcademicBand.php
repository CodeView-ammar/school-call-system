<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicBand extends Model
{
    use HasFactory;

    protected $fillable = ['school_id', 'education_level_id', 'name_ar', 'name_en', 'short_name', 'is_active'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function educationLevel()
    {
        return $this->belongsTo(EducationLevel::class);
    }

    public function weekDays()
    {
        return $this->belongsToMany(WeekDay::class, 'academic_band_week_days', 'academic_band_id', 'week_day_id')
            ->withPivot(['school_id', 'start_time', 'end_time', 'is_active', 'notes'])
            ->withTimestamps();
    }
    public function students()
    {
        return $this->belongsToMany(Student::class, 'academic_band_student')
            ->withTimestamps();
    }
    public function academicBandWeekDays()
    {
        return $this->hasMany(AcademicBandWeekDay::class);
    }
}