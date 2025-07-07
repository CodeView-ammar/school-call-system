<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'education_level_id',
        'name_ar',
        'name_en',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'school_class_id');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?? $this->name_en;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByEducationLevel($query, $levelId)
    {
        return $query->where('education_level_id', $levelId);
    }
}