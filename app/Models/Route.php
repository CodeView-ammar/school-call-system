<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    // اسم الجدول (اختياري إذا اتبعنا convention، Laravel يعتبر 'routes' تلقائيًا)
    protected $table = 'routes';

    // الحقول القابلة للتعبئة (fillable)
    protected $fillable = [
        'name',
        'school_id',

    ];

    // العلاقة مع المدرسة
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function stops()
    {
        return $this->belongsToMany(Stop::class, 'route_stop', 'route_id', 'stop_id');
    }
}
