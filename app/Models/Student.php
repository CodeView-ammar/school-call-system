<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'school_id',
        'branch_id',
        'education_level_id',
        'grade_id',
        'school_class_id',
        'bus_route_id',
        'code',
        'student_number',
        'name',
        'name_ar',
        'name_en',
        'national_id',
        'date_of_birth',
        'gender',
        'nationality',
        'photo',
        'address_ar',
        'address_en',
        'latitude',
        'longitude',
        'medical_notes',
        'emergency_contact',
        'bus_id',
        'pickup_location',
        'is_active'
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];
    
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    
    public function schoolClass()
    {
        return $this->belongsTo(GradeClass::class, 'school_class_id');
    }
    
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
    
    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardians')
            ->withPivot('is_primary')
            ->withTimestamps();
    }
    
    public function primaryGuardian()
    {
        return $this->guardians()->wherePivot('is_primary', true)->first();
    }
    
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->diffInYears(now()) : null;
    }
    
    public function getFullNameAttribute()
    {
        return $this->name_ar ?? $this->name_en;
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
    
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }
    
}
