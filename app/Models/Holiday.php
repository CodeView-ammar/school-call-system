<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_name_ar',
        'holiday_name_en',
        'holiday_from_date',
        'holiday_to_date',
        'holiday_isactive',
        'holiday_cust_code',
        'holiday_cdate',
        'holiday_udate',
    ];

    protected $casts = [
        'holiday_from_date' => 'date',
        'holiday_to_date' => 'date',
        'holiday_cdate' => 'datetime',
        'holiday_udate' => 'datetime',
        'holiday_isactive' => 'boolean',
    ];

    // نطاقات
    public function scopeActive($query)
    {
        return $query->where('holiday_isactive', '1');
    }

    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('holiday_from_date', '<=', $today)
                    ->where('holiday_to_date', '>=', $today);
    }

    // دوال مساعدة
    public function isCurrentlyActive()
    {
        $today = now()->toDateString();
        return $this->holiday_isactive && 
               $this->holiday_from_date <= $today && 
               $this->holiday_to_date >= $today;
    }
}