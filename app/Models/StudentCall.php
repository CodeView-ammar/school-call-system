<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCall extends Model
{
    use HasFactory;

    protected $table = 'student_calls';
    protected $primaryKey = 'id';
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
        'call_period',
    ];



    public const PERIOD_MORNING = 'morning';
    public const PERIOD_EVENING = 'evening';

    public static function getPeriods(): array
    {
        return [
            self::PERIOD_MORNING => 'صباحي',
            self::PERIOD_EVENING => 'مسائي',
        ];
    }
    public function getPeriodNameAttribute(): string
    {
        return self::getPeriods()[$this->call_period] ?? 'غير محدد';
    }

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

    // حالات خاصة بفترة الصباح
    public const STATUS_MORNING_PREPARE = 'morning_prepare';    // استعداد
    public const STATUS_MORNING_ARRIVED = 'morning_arrived';    // تم الوصول
    public const STATUS_MORNING_RECEIVED = 'morning_received';  // استلام الطالب
    public const STATUS_MORNING_DELIVERED = 'morning_delivered'; // تسليم للمدرسة
    public const STATUS_MORNING_CANCELED = 'morning_canceled';  // إلغاء صباحي

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
            // حالات الصباح
            self::STATUS_MORNING_PREPARE   => 'استعداد',
            self::STATUS_MORNING_ARRIVED   => 'تم الوصول',
            self::STATUS_MORNING_RECEIVED  => 'استلام الطالب',
            self::STATUS_MORNING_DELIVERED => 'تسليم للمدرسة',
            self::STATUS_MORNING_CANCELED  => 'تم الإلغاء',
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

    /**
     * الحصول على حالات الصباح المتاحة
     */
    public static function getMorningStatuses(): array
    {
        return [
            self::STATUS_MORNING_PREPARE,
            self::STATUS_MORNING_ARRIVED,
            self::STATUS_MORNING_RECEIVED,
            self::STATUS_MORNING_DELIVERED,
            self::STATUS_MORNING_CANCELED,
        ];
    }

    /**
     * التحقق من كون الحالة خاصة بالصباح
     */
    public function isMorningStatus(): bool
    {
        return in_array($this->status, self::getMorningStatuses());
    }

    /**
     * التحقق من كون فترة النداء صباحية
     */
    public function isMorningPeriod(): bool
    {
        return $this->call_period === self::PERIOD_MORNING;
    }

    /**
     * الحصول على الحالة الافتراضية حسب فترة النداء
     */
    public static function getDefaultStatusByPeriod(string $period): string
    {
        return $period === self::PERIOD_MORNING 
            ? self::STATUS_MORNING_PREPARE 
            : self::STATUS_PREPARE;
    }

    /**
     * التحقق من صحة الحالة للفترة المحددة
     */
    public function isValidStatusForPeriod(string $status, string $period): bool
    {
        if ($period === self::PERIOD_MORNING) {
            return in_array($status, self::getMorningStatuses());
        }
        
        // للفترة المسائية، استخدم الحالات العادية
        $eveningStatuses = [
            self::STATUS_PREPARE,
            self::STATUS_LEAVE,
            self::STATUS_WITH_TEACHER,
            self::STATUS_TO_GATE,
            self::STATUS_RECEIVED,
            self::STATUS_CANCELED,
            self::STATUS_HOMEWARD,
            self::STATUS_ARRIVED_HOME,
            self::STATUS_DELIVERED,
        ];
        
        return in_array($status, $eveningStatuses);
    }
}