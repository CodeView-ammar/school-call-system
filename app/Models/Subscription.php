<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'type_id',
        'name',
        'price',
        'max_students',
        'max_calls',
        'max_bus',
        'max_classes',
        'max_users',
        'max_drivers',
        'max_schools_branch',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // علاقات
    public function type()
    {
        return $this->belongsTo(SubscriptionType::class, 'type_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class, 'subscription_id');
    }

    // نطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
