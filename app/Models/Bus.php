<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'driver_id',      // أضف هنا
        'supervisor_id', 
        'number',
        "plate_number", 

        'driver_name',
        'driver_phone',
        'capacity',
        'route_description',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];


    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
    // العلاقات
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'bus_id', 'id');
    }
    public function busRoutes()
    {
        return $this->hasMany(BusRoute::class);
    }

    // Accessors
    public function getAvailableSeatsAttribute(): int
    {
        return $this->capacity - $this->students->count();
    }

    public function getIsFullAttribute(): bool
    {
        return $this->available_seats <= 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeAvailable($query)
    {
        return $query->whereRaw('capacity > (SELECT COUNT(*) FROM students WHERE students.bus_id = buses.id)');
    }
}