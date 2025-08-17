<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'effective_date',
        'repeated_every_days',
        'arrival_time_at_first_stop',
        'stop_to_stop_time_minutes',
        'driver_id',
        'bus_id',
        'school_id',
        'is_active'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'arrival_time_at_first_stop' => 'string',
        'stop_to_stop_time_minutes' => 'integer',
        'repeated_every_days' => 'integer',
        'is_active' => 'boolean',
    ];

    // العلاقة مع المسار
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    // العلاقة مع السائق
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // العلاقة مع الباص
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    // العلاقة مع المدرسة
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // العلاقة مع أوقات المحطات
    public function tripStops()
    {
        return $this->hasMany(TripStop::class)->orderBy('stop_order');
    }

    // جلب أوقات المحطات المرتبة
    public function getOrderedStopsAttribute()
    {
        return $this->tripStops()->with('stop')->orderBy('stop_order')->get();
    }
}