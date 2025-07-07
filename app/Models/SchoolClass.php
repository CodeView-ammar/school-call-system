<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'education_level_id',
        'grade_id',
        'name',
        'name_ar',
        'class_code',
        'capacity',
        'current_students',
        'classroom_number',
        'academic_year',
        'is_active'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'current_students' => 'integer',
        'is_active' => 'boolean'
    ];

    // العلاقات
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    public function getAvailableSeatsAttribute(): int
    {
        return max(0, $this->capacity - $this->current_students);
    }

    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->capacity > 0) {
            return round(($this->current_students / $this->capacity) * 100, 2);
        }
        return 0;
    }

    public function getIsFullAttribute(): bool
    {
        return $this->current_students >= $this->capacity;
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'غير نشط';
        }
        
        if ($this->is_full) {
            return 'مكتمل';
        }
        
        $percentage = $this->occupancy_percentage;
        if ($percentage >= 90) {
            return 'شبه مكتمل';
        } elseif ($percentage >= 50) {
            return 'نصف مكتمل';
        } else {
            return 'متاح';
        }
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForEducationLevel(Builder $query, int $educationLevelId): Builder
    {
        return $query->where('education_level_id', $educationLevelId);
    }

    public function scopeForGrade(Builder $query, int $gradeId): Builder
    {
        return $query->where('grade_id', $gradeId);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereRaw('current_students < capacity');
    }

    public function scopeFull(Builder $query): Builder
    {
        return $query->whereRaw('current_students >= capacity');
    }

    public function scopeAcademicYear(Builder $query, string $year): Builder
    {
        return $query->where('academic_year', $year);
    }
}