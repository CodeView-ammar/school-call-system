<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CallType;

class CallTypesSeeder extends Seeder
{
    public function run(): void
    {
        $callTypes = [
            [
                'ctype_name_ar' => 'مكالمة حضور',
                'ctype_name_eng' => 'Attendance Call',
                'ctype_isactive' => 1,
            ],
            [
                'ctype_name_ar' => 'مكالمة غياب',
                'ctype_name_eng' => 'Absence Call',
                'ctype_isactive' => 1,
            ],
            [
                'ctype_name_ar' => 'مكالمة تأخير',
                'ctype_name_eng' => 'Late Call',
                'ctype_isactive' => 1,
            ],
            [
                'ctype_name_ar' => 'مكالمة خروج مبكر',
                'ctype_name_eng' => 'Early Exit Call',
                'ctype_isactive' => 1,
            ],
            [
                'ctype_name_ar' => 'مكالمة طوارئ',
                'ctype_name_eng' => 'Emergency Call',
                'ctype_isactive' => 1,
            ],
            [
                'ctype_name_ar' => 'مكالمة إعلامية',
                'ctype_name_eng' => 'Informational Call',
                'ctype_isactive' => 1,
            ],
        ];

        foreach ($callTypes as $callType) {
            CallType::firstOrCreate(
                ['ctype_name_ar' => $callType['ctype_name_ar']],
                $callType
            );
        }
    }
}