<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'stop_id',
        'arrival_time',
        'stop_order',
        'is_pickup',
        'is_dropoff'
    ];

    protected $casts = [
        'arrival_time' => 'time',
        'stop_order' => 'integer',
        'is_pickup' => 'boolean',
        'is_dropoff' => 'boolean',
    ];

    // العلاقة مع الرحلة
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    // العلاقة مع المحطة
    public function stop()
    {
        return $this->belongsTo(Stop::class);
    }

    // ترتيب حسب ترتيب المحطة
    public function scopeOrdered($query)
    {
        return $query->orderBy('stop_order');
    }
}