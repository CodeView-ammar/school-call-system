<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $table = 'licenses';
    protected $primaryKey = 'id'; // تم تغيير المفتاح الأساسي

    protected $fillable = [
        'school_id',
        'subscription_id',
        'starts_at',
        'ends_at',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];
    protected static function booted()
    {
        static::creating(function ($license) {
            if (auth()->check()) {
                $license->created_by = auth()->user()->id;
            }
        });
    }
    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    
    // نطاقات (Scopes)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('ends_at', '>=', now());
    }
}
