<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicBandWeekDay;
use App\Models\School;
use App\Models\AcademicBand;
use App\Models\WeekDay;

class AcademicBandWeekDaySeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::with(['academicBands', 'weekDays'])->get();

        foreach ($schools as $school) {
            if ($school->academicBands->isEmpty() || $school->weekDays->isEmpty()) {
                continue;
            }

            foreach ($school->academicBands as $band) {
                foreach ($school->weekDays->where('day_inactive', '!=', 1) as $weekDay) {
                    // إنشاء جدولة عشوائية للفرق
                    $startHour = rand(7, 9);
                    $endHour = $startHour + rand(6, 8);

                    AcademicBandWeekDay::create([
                        'school_id' => $school->id,
                        'academic_band_id' => $band->id,
                        'week_day_id' => $weekDay->day_id,
                        'start_time' => sprintf('%02d:00:00', $startHour),
                        'end_time' => sprintf('%02d:00:00', min($endHour, 17)),
                        'is_active' => rand(0, 1) ? true : false,
                        'notes' => rand(0, 1) ? 'جدولة افتراضية للفرقة' : null,
                    ]);
                }
            }
        }

        $this->command->info('✅ تم إنشاء جداول الفرق الدراسية بنجاح');
    }
}
