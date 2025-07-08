<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicBand extends Model
{
    use HasFactory;

    protected $fillable = ['school_id', 'education_level_id', 'name_ar', 'name_en', 'short_name', 'is_active'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function educationLevel()
    {
        return $this->belongsTo(EducationLevel::class);
    }
}