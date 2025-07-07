<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $table = 'licenses';
    protected $primaryKey = 'lic_id';

    protected $fillable = [
        'lic_start_at',
        'lic_end_at',
        'lic_by_user',
        'lic_cdate',
        'lic_udate',
        'lic_cust_code',
        'lic_isactive',
    ];

    protected $casts = [
        'lic_start_at' => 'date',
        'lic_end_at' => 'date',
        'lic_cdate' => 'datetime',
        'lic_udate' => 'datetime',
        'lic_isactive' => 'boolean',
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class, 'lic_by_user');
    }

    // نطاقات
    public function scopeActive($query)
    {
        return $query->where('lic_isactive', '1');
    }

    public function scopeValid($query)
    {
        return $query->where('lic_end_at', '>=', now()->toDateString());
    }
}