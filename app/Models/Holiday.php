<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',               // الاسم العربي
        'name_en',               // الاسم الإنجليزي
        'from_date',             // تاريخ البداية
        'to_date',               // تاريخ النهاية
        'is_active',             // حالة النشاط
        'created_at',            // تاريخ الإنشاء
        'updated_at',            // تاريخ التحديث
        'school_id',             // إضافة علاقة مع المدرسة
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'is_active' => 'boolean',
    ];

    // علاقة مع المدرسة
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // نطاقات
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('from_date', '<=', $today)
                    ->where('to_date', '>=', $today);
    }

    // دوال مساعدة
    public function isCurrentlyActive()
    {
        $today = now()->toDateString();
        return $this->is_active && 
               $this->from_date <= $today && 
               $this->to_date >= $today;
    }

    
}