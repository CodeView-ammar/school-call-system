<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name_ar',
        'name_en',
        'phone',
        'email',
        'national_id',
        'relationship',
        'address_ar',
        'address_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withTimestamps()
            ->withPivot('is_primary');
    }

    public function primaryStudents(): BelongsToMany
    {
        return $this->students()->wherePivot('is_primary', true);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?? $this->name_en;
    }

    public function getRelationshipLabelAttribute(): string
    {
        return match($this->relationship) {
            'father' => 'والد',
            'mother' => 'والدة',
            'grandfather' => 'جد',
            'grandmother' => 'جدة',
            'uncle' => 'عم/خال',
            'aunt' => 'عمة/خالة',
            'other' => 'أخرى',
            default => $this->relationship,
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRelationship($query, $relationship)
    {
        return $query->where('relationship', $relationship);
    }
}