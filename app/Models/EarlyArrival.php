<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class EarlyArrival extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'early_arrivals'; // تحديد اسم الجدول

    protected $fillable = [
        'student_id',
        'guardian_id',
        'school_id',
        'branch_id',
        'user_id',
        'pickup_date',
        'pickup_time',
        'pickup_reason',
        'status',
    ];

    // علاقات مع الجداول الأخرى
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}