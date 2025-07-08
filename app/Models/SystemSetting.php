<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';
    protected $primaryKey = 'sys_id';

    protected $fillable = [
        'school_id',
        'sys_earlyexit',
        'sys_earlycall',
        'sys_return_call',
        'sys_exit_togat',
        'sys_cust_code',
        'sys_cdate',
        'sys_udate',
    ];

    protected $casts = [
        'sys_cdate' => 'datetime',
        'sys_udate' => 'datetime',
    ];

    /**
     * العلاقة مع المدرسة
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * ضمان وجود سجل واحد فقط لكل مدرسة
     */
    public static function getSingleInstanceForSchool($schoolId)
    {
        $setting = static::where('school_id', $schoolId)->first();
        
        if (!$setting) {
            $setting = static::create([
                'school_id' => $schoolId,
                'sys_earlycall' => '07:00',
                'sys_return_call' => '15:00',
                'sys_earlyexit' => '14:00', 
                'sys_exit_togat' => '12:00',
                'sys_cust_code' => '',
                'sys_cdate' => now(),
                'sys_udate' => now(),
            ]);
        }
        
        return $setting;
    }

    /**
     * منع إنشاء أكثر من سجل واحد لكل مدرسة
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // إذا كان هناك سجل موجود بالفعل لنفس المدرسة، منع إنشاء سجل جديد
            if (static::where('school_id', $model->school_id)->exists()) {
                return false;
            }
        });
    }

    /**
     * نطاق للحصول على الإعدادات الخاصة بمدرسة معينة
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}