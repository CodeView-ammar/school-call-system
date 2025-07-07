<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'user_id',
        'employee_id',
        'name',
        'name_ar',
        'phone',
        'email',
        'license_number',
        'license_expiry',
        'experience_years',
        'hire_date',
        'salary',
        'emergency_contact_name',
        'emergency_contact_phone',
        'is_active'
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'experience_years' => 'integer',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }

    public function callSessions(): HasMany
    {
        return $this->hasMany(CallSession::class);
    }

    public function driverShifts(): HasMany
    {
        return $this->hasMany(DriverShift::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    public function getLicenseStatusAttribute(): string
    {
        if (!$this->license_expiry) {
            return 'غير محدد';
        }

        $daysToExpiry = now()->diffInDays($this->license_expiry, false);
        
        if ($daysToExpiry < 0) {
            return 'منتهية الصلاحية';
        } elseif ($daysToExpiry <= 30) {
            return 'تنتهي قريباً';
        } else {
            return 'سارية';
        }
    }

    public function getServiceYearsAttribute(): int
    {
        if ($this->hire_date) {
            return $this->hire_date->diffInYears(now());
        }
        return 0;
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

    public function scopeWithValidLicense(Builder $query): Builder
    {
        return $query->where('license_expiry', '>', now());
    }

    public function scopeLicenseExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('license_expiry', [
            now(),
            now()->addDays($days)
        ]);
    }

    public function scopeExperienceLevel(Builder $query, string $level): Builder
    {
        return match($level) {
            'junior' => $query->where('experience_years', '<=', 2),
            'mid' => $query->whereBetween('experience_years', [3, 7]),
            'senior' => $query->where('experience_years', '>=', 8),
            default => $query
        };
    }
}