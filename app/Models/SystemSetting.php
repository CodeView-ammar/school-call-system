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
}