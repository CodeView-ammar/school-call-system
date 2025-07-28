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
        'academic_band_id',
        'name_ar',
        'name_en',
        'code',
        'is_active',
        "branch_id",
        "school_id",
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'school_class_id');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    // العلاقات
    
    public function academicBand()
    {
        return $this->belongsTo(AcademicBand::class, 'academic_band_id');
    }
    

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
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