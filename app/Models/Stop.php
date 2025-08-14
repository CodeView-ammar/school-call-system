<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stop extends Model
{
    protected $fillable = [
         'school_id', 'name', 'address', 'latitude', 'longitude', 'description', 'is_active'
    ];

    // العلاقة مع المدرسة
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    
    public function routes()
{
    return $this->belongsToMany(Route::class, 'route_stop', 'stop_id', 'route_id');
}
}
