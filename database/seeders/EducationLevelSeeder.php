<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EducationLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $educationLevels = [
            ['name_ar' => 'رياض الأطفال', 'name_en' => 'Kindergarten', 'short_name' => 'KG'],
            ['name_ar' => 'المرحلة الابتدائية', 'name_en' => 'Elementary', 'short_name' => 'ELM'],
            ['name_ar' => 'المرحلة المتوسطة', 'name_en' => 'Middle School', 'short_name' => 'MID'],
            ['name_ar' => 'المرحلة الثانوية', 'name_en' => 'High School', 'short_name' => 'HIGH'],
        ];

        foreach ($educationLevels as $level) {
            \App\Models\EducationLevel::create($level);
        }
    }
}
