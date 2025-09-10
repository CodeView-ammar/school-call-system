<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_ar',
        'school_id',
        'branch_id',   // أضف هذا
        'route_type',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stops()
    {
        return $this->belongsToMany(Stop::class, 'route_stop', 'route_id', 'stop_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
