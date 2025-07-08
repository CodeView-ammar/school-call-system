<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaysSeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            [
                'name_ar' => 'عيد الفطر المبارك',
                'name_en' => 'Eid Al-Fitr',
                'from_date' => '2025-04-09',
                'to_date' => '2025-04-12',
                'isactive' => '1',
            ],
            [
                'name_ar' => 'عيد الأضحى المبارك',
                'name_en' => 'Eid Al-Adha',
                'from_date' => '2025-06-15',
                'to_date' => '2025-06-18',
                'isactive' => '1',
            ],
            [
                'name_ar' => 'اليوم الوطني السعودي',
                'name_en' => 'Saudi National Day',
                'from_date' => '2025-09-23',
                'to_date' => '2025-09-23',
                'isactive' => '1',
            ],
            [
                'name_ar' => 'إجازة نصف العام',
                'name_en' => 'Mid-Year Break',
                'from_date' => '2025-01-20',
                'to_date' => '2025-01-31',
                'isactive' => '1',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                ['name_ar' => $holiday['name_ar']],
                $holiday
            );
        }
    }
}