<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCall extends Model
{
    protected $fillable = [
        'call_type_id', 'student_id', 'school_id', 'call_cdate', 'call_edate', 'user_id', 'branch_id', 'status'
    ];

    public function callType()
    {
        return $this->belongsTo(CallType::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}