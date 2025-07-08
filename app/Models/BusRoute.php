<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'bus_id',
        'name_ar',
        'name_en',
        'code',
        'route_road_from_lat',
        'route_road_from_lng',
        'route_road_from_address',
        'route_road_to_lat',
        'route_road_to_lng',
        'route_road_to_address',
        'route_is_go',
        'route_is_return',
        'estimated_time',
        'distance_km',
        'description',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'route_road_from_lat' => 'decimal:8',
        'route_road_from_lng' => 'decimal:8',
        'route_road_to_lat' => 'decimal:8',
        'route_road_to_lng' => 'decimal:8',
        'route_is_go' => 'boolean',
        'route_is_return' => 'boolean',
        'distance_km' => 'decimal:2',
        'estimated_time' => 'integer',
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // نطاقات للاستعلام
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeGoRoute($query)
    {
        return $query->where('route_is_go', true);
    }

    public function scopeReturnRoute($query)
    {
        return $query->where('route_is_return', true);
    }

    // دوال مساعدة
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?? $this->name_en;
    }

    public function getRouteTypeAttribute(): string
    {
        if ($this->route_is_go && $this->route_is_return) {
            return 'ذهاب وعودة';
        } elseif ($this->route_is_go) {
            return 'ذهاب';
        } elseif ($this->route_is_return) {
            return 'عودة';
        }
        return 'غير محدد';
    }

    public function getFromLocationAttribute(): array
    {
        return [
            'lat' => $this->route_road_from_lat,
            'lng' => $this->route_road_from_lng,
            'address' => $this->route_road_from_address,
        ];
    }

    public function getToLocationAttribute(): array
    {
        return [
            'lat' => $this->route_road_to_lat,
            'lng' => $this->route_road_to_lng,
            'address' => $this->route_road_to_address,
        ];
    }
}
