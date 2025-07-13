<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Supervisor extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'user_id',
        'employee_id',
        'name',
        'phone',
        'email',
        'created_at',
        "guardians",
        'position',
        'salary',
        'is_active'
    ];

    protected $casts = [
        'created_at' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    
    // العلاقات
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'guardian_supervisor');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buses(): BelongsToMany
    {
        return $this->belongsToMany(Bus::class, 'supervisor_buses')
                    ->withPivot(['assigned_at', 'is_primary'])
                    ->withTimestamps();
    }

    public function callSessions(): HasMany
    {
        return $this->hasMany(CallSession::class);
    }



    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_supervisor');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    public function getServiceYearsAttribute(): int
    {
        if ($this->created_at) {
            return $this->created_at->diffInYears(now());
        }
        return 0;
    }
    public function toggleStatus(): void
    {
        $this->update([
            'is_active' => !$this->is_active,
        ]);
    }
    public function getAssignedBusesCountAttribute(): int
    {
        return $this->buses()->count();
    }

    public function getAssignedStudentsCountAttribute(): int
    {
        return $this->students()->count();
    }

    public function getMonthlyCallSessionsAttribute(): int
    {
        return $this->callSessions()
                    ->whereMonth('session_date', now()->month)
                    ->whereYear('session_date', now()->year)
                    ->count();
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


    public function scopeByPosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }

    public function scopeWithBuses(Builder $query): Builder
    {
        return $query->has('buses');
    }

    public function scopeWithoutBuses(Builder $query): Builder
    {
        return $query->doesntHave('buses');
    }

    // Helper Methods
    public function assignToBus(int $busId, bool $isPrimary = false): void
    {
        $this->buses()->attach($busId, [
            'assigned_at' => now(),
            'is_primary' => $isPrimary
        ]);
    }

    public function removeBus(int $busId): void
    {
        $this->buses()->detach($busId);
    }

    public function getPrimaryBuses()
    {
        return $this->buses()->wherePivot('is_primary', true);
    }
}