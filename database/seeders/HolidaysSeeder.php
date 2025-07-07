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
                'holiday_name_ar' => 'عيد الفطر المبارك',
                'holiday_name_en' => 'Eid Al-Fitr',
                'holiday_from_date' => '2025-04-09',
                'holiday_to_date' => '2025-04-12',
                'holiday_isactive' => '1',
            ],
            [
                'holiday_name_ar' => 'عيد الأضحى المبارك',
                'holiday_name_en' => 'Eid Al-Adha',
                'holiday_from_date' => '2025-06-15',
                'holiday_to_date' => '2025-06-18',
                'holiday_isactive' => '1',
            ],
            [
                'holiday_name_ar' => 'اليوم الوطني السعودي',
                'holiday_name_en' => 'Saudi National Day',
                'holiday_from_date' => '2025-09-23',
                'holiday_to_date' => '2025-09-23',
                'holiday_isactive' => '1',
            ],
            [
                'holiday_name_ar' => 'إجازة نصف العام',
                'holiday_name_en' => 'Mid-Year Break',
                'holiday_from_date' => '2025-01-20',
                'holiday_to_date' => '2025-01-31',
                'holiday_isactive' => '1',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                ['holiday_name_ar' => $holiday['holiday_name_ar']],
                $holiday
            );
        }
    }
}