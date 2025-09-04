<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionType extends Model
{
    use HasFactory;

    protected $table = 'subscription_types'; // جدول الاشتراكات النوعية
    protected $primaryKey = 'id';

    protected $fillable = [
        'name_ar',
        'name_en',
        'created_at',
        'updated_at',
    ];

    // علاقات
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'subscription_type_id');
    }
}
