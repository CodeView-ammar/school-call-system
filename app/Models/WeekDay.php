<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekDay extends Model
{
    use HasFactory;

    protected $table = 'week_days';
    protected $primaryKey = 'day_id';

    protected $fillable = [
        'day',
        'time_to',
        'time_from',
        'day_inactive',
        'branch_code',
        'customer_code',
        'band_id',
    ];

    protected $casts = [
        'time_to' => 'datetime:H:i:s',
        'time_from' => 'datetime:H:i:s',
    ];

    // العلاقات
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_code', 'code');
    }
}