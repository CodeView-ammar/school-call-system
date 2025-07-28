<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCallLog extends Model
{
    // اسم الجدول (لو ما اتبع اسم الموديل بصيغة الجمع)
    protected $table = 'student_calls_log';

    // الحقول اللي يمكن تعبئتها (fillable) للحماية من التعيين الجماعي
    protected $fillable = [
        'student_call_id',
        'status',
        'changed_at',
        'changed_by_user_id',
    ];

    // تاريخ التغيير 'changed_at' يعتبر حقل تاريخ، فنعرفه هنا كـ Carbon instance
    protected $dates = ['changed_at'];
    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * علاقة سجل النداء بالنداء نفسه
     */
    public function studentCall(): BelongsTo
    {
        return $this->belongsTo(StudentCall::class, 'student_call_id', 'call_id');
        // 'student_call_id' هو المفتاح الأجنبي في هذا الجدول
        // 'call_id' هو المفتاح الأساسي في جدول student_calls
    }

    /**
     * علاقة سجل النداء بالمستخدم الذي قام بالتغيير
     */
    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
        // هنا المفتاح الأساسي في users هو 'id' وهذا هو الافتراضي
    }
}
