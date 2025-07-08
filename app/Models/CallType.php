<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallType extends Model
{
    use HasFactory;

    protected $table = 'call_types';
    protected $primaryKey = 'ctype_id';

    protected $fillable = [
        'ctype_name_eng',
        'ctype_name_ar',
        'ctype_cdate',
        'ctype_udate',
        'ctype_isactive',
        'school_id',
    ];

    protected $casts = [
        'ctype_isactive' => 'boolean',
        'ctype_cdate' => 'datetime',
        'ctype_udate' => 'datetime',
    ];

    // نطاقات
    public function scopeActive($query)
    {
        return $query->where('ctype_isactive', 1);
    }
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}