<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'education_level_id',
        'name',
        'name_ar',
        'grade_number',
        'description',
        'min_age',
        'max_age',
        'is_active'
    ];

    protected $casts = [
        'grade_number' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
        'is_active' => 'boolean'
    ];

    // العلاقات
    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }

    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    public function getAgeRangeAttribute(): string
    {
        if ($this->min_age && $this->max_age) {
            return "{$this->min_age} - {$this->max_age} سنة";
        }
        return 'غير محدد';
    }

    public function getStudentsCountAttribute(): int
    {
        return $this->students()->count();
    }

    public function getClassesCountAttribute(): int
    {
        return $this->schoolClasses()->count();
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForEducationLevel(Builder $query, int $educationLevelId): Builder
    {
        return $query->where('education_level_id', $educationLevelId);
    }

    public function scopeByGradeNumber(Builder $query, int $gradeNumber): Builder
    {
        return $query->where('grade_number', $gradeNumber);
    }

    public function scopeOrderByGrade(Builder $query): Builder
    {
        return $query->orderBy('grade_number');
    }
}