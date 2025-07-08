<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class BusRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'bus_id',
        'name',
        'name_ar',
        'route_code',
        'start_location',
        'end_location',
        'start_time',
        'end_time',
        'estimated_duration',
        'distance_km',
        'stops_count',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'distance_km' => 'decimal:2',
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

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    public function callSessions(): HasMany
    {
        return $this->hasMany(CallSession::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->name_ar . ' - ' . $this->name;
    }

    public function getDurationInMinutesAttribute(): int
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }
        return $this->estimated_duration ?? 0;
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

    public function scopeForBus(Builder $query, int $busId): Builder
    {
        return $query->where('bus_id', $busId);
    }
}