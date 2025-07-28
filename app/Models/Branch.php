<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    
    public function school()
    {
        return $this->belongsTo(School::class);
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
