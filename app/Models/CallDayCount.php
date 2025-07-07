<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallDayCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'student_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // العلاقات
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}