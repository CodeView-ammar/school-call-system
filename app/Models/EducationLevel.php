<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationLevel extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'school_id',
        'name_ar',
        'name_en',
        'short_name',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ✅ العلاقة مع الصفوف الدراسية
    public function gradeClasses(): HasMany
    {
        return $this->hasMany(GradeClass::class);
    }

    // ✅ العلاقة مع المدرسة
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?? $this->name_en;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
