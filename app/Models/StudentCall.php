<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCall extends Model
{
    use HasFactory;

    protected $table = 'student_calls';
    protected $primaryKey = 'call_id';
    public $incrementing = true; // إذا كان auto-increment
    protected $keyType = 'int';  // نوع المفتاح
    protected $fillable = [
        'student_id',
        'school_id',
        'call_cdate',
        'call_edate',
        'user_id',
        'branch_id',
        'status',
        'caller_type',
        'call_level',
    ];

    // تعريف الثوابت لحالات الاستدعاء
    public const STATUS_PREPARE = 'prepare';            // طلب الاستعداد
    public const STATUS_LEAVE = 'leave';                // طلب المغادرة
    public const STATUS_WITH_TEACHER = 'with_teacher';  // مع المعلم
    public const STATUS_TO_GATE = 'to_gate';            // في الطريق إلى البوابة
    public const STATUS_RECEIVED = 'received';          // تم استلام الطالب
    public const STATUS_CANCELED = 'canceled';          // إلغاء
    public const STATUS_HOMEWARD = 'homeward';          // في الطريق إلى المنزل
    public const STATUS_ARRIVED_HOME = 'arrived_home';  // تم الوصول إلى المنزل
    public const STATUS_DELIVERED = 'delivered';        // تم التسليم

    // قائمة الحالات مع الترجمة العربية
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PREPARE      => 'طلب الاستعداد',
            self::STATUS_LEAVE        => 'طلب المغادرة',
            self::STATUS_WITH_TEACHER => 'مع المعلم',
            self::STATUS_TO_GATE      => 'في الطريق إلى البوابة',
            self::STATUS_RECEIVED     => 'تم استلام الطالب',
            self::STATUS_CANCELED     => 'تم الإلغاء',
            self::STATUS_HOMEWARD      => 'في الطريق إلى المنزل',
            self::STATUS_ARRIVED_HOME  => 'تم الوصول إلى المنزل',
            self::STATUS_DELIVERED     => 'تم التسليم',
        ];
    }

    // Accessor لإرجاع اسم الحالة النصي
    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'غير معروف';
    }

    // العلاقات
    public function latestLog()
    {
        return $this->hasOne(StudentCallLog::class, 'student_call_id')
                    ->latestOfMany('changed_at');  
    }
    
    public function student() { return $this->belongsTo(Student::class); }
    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    
    public function studentCallLogs()
    {
        return $this->hasMany(StudentCallLog::class, 'student_call_id'); // استخدم hasMany إذا كان لديك سجلات متعددة
    }
}