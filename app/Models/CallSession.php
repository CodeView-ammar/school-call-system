<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CallSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'bus_route_id',
        'supervisor_id',
        'driver_id',
        'session_date',
        'session_type',
        'start_time',
        'end_time',
        'status',
        'total_students',
        'called_students',
        'present_students',
        'absent_students',
        'notes'
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_students' => 'integer',
        'called_students' => 'integer',
        'present_students' => 'integer',
        'absent_students' => 'integer'
    ];

    // Constants
    const SESSION_TYPES = [
        'morning_pickup' => 'استقبال صباحي',
        'afternoon_dropoff' => 'توصيل مسائي',
        'special_trip' => 'رحلة خاصة'
    ];

    const STATUSES = [
        'pending' => 'في الانتظار',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي'
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

    public function busRoute(): BelongsTo
    {
        return $this->belongsTo(BusRoute::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function studentCalls(): HasMany
    {
        return $this->hasMany(StudentCall::class);
    }

    // Accessors
    public function getSessionTypeTextAttribute(): string
    {
        return self::SESSION_TYPES[$this->session_type] ?? $this->session_type;
    }

    public function getStatusTextAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getDurationInMinutesAttribute(): ?int
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }
        return null;
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_students > 0) {
            return round(($this->called_students / $this->total_students) * 100, 2);
        }
        return 0;
    }

    // Scopes
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForSupervisor(Builder $query, int $supervisorId): Builder
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    public function scopeForDriver(Builder $query, int $driverId): Builder
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeBySessionType(Builder $query, string $sessionType): Builder
    {
        return $query->where('session_type', $sessionType);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('session_date', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('session_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }
}