<?php

namespace App\Filament\Resources\WeekDayResource\Pages;

use App\Filament\Resources\WeekDayResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\WeekDay;
class CreateWeekDay extends CreateRecord
{
    protected static string $resource = WeekDayResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // نحذف اليوم لأنه مجموعة وسننشيء سجلات يدوياً
        return $data;
    }

    // protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    // {
    //     // ننشيء السجلات يدويًا حسب الأيام المختارة
    //     dd($data['days']);
    //     foreach ($data['days'] as $day) {
    //         WeekDay::create([
    //             'school_id'     => $data['school_id'] ?? auth()->user()?->school_id,
    //             'branch_id'     => $data['branch_id'],
    //             'day'           => $day,
    //             'time_from'     => $data['time_from'],
    //             'time_to'       => $data['time_to'],
    //             'day_inactive'  => $data['day_inactive'] ,
    //             'band_id'       => $data['band_id'] ?? 1,
    //             'branch_code'   => $data['branch_code'] ?? '',
    //             'customer_code' => $data['customer_code'] ?? '',
    //         ]);
    //     }

    //     // نعيد Model وهمي فقط لتجنب الخطأ (لن يُستخدم)
    //     return new \App\Models\WeekDay();
    // }

    protected function getRedirectUrl(): string
    {
        return WeekDayResource::getUrl('index');
    }

}
