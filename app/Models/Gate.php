<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gate extends Model
{
 protected $fillable = ['name', 'school_id', 'is_active'];
     //
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function academicBands()
    {
        return $this->hasMany(AcademicBand::class);
    }
    

}
