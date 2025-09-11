<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StudentBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'backup_name',
        'file_path',
        'file_size',
        'students_count',
        'notes',
        'created_by',
        'last_restored_at',
        'restored_by'
    ];

    protected $casts = [
        'last_restored_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'file_size' => 'integer',
        'students_count' => 'integer'
    ];

    /**
     * العلاقة مع المدرسة
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ النسخة الاحتياطية
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع المستخدم الذي استرد النسخة الاحتياطية
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * تنسيق حجم الملف
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * التحقق من وجود الملف
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }


// public function file_exists(): bool
// {
//     return Storage::disk('local')->exists($this->file_path);
// }

    /**
     * سكوب للحصول على النسخ الاحتياطية للمدرسة المحددة
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * سكوب للحصول على النسخ الاحتياطية الحديثة
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}