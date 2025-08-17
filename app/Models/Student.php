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
        'academic_band_id',
        'grade_class_id',
        'bus_route_id',
        'code',
        'student_number',
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
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
    
    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withTimestamps()
            ->withPivot('is_primary');
    }
    public function academicBand()
    {
        return $this->belongsTo(AcademicBand::class);
    }
    public function primaryGuardian()
    {
        return $this->guardians()->wherePivot('is_primary', true)->first();
    }
    public function supervisors()
{
    return $this->belongsToMany(Supervisor::class, 'student_supervisor');
}
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function stop()
    {
        return $this->hasMany(Stop::class);
    }
    
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->diffInYears(now()) : null;
    }
    public function earlyArrivals()
    {
        return $this->hasMany(EarlyArrival::class);
    }
    public function gradeClass()
    {
        return $this->belongsTo(GradeClass::class, 'grade_class_id');
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
