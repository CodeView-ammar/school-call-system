<?php

namespace App\Filament\Resources\AcademicBandWeekDayResource\Pages;

use App\Filament\Resources\AcademicBandWeekDayResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademicBandWeekDay extends CreateRecord
{
    protected static string $resource = AcademicBandWeekDayResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تأكد من وجود school_id
        if (!isset($data['school_id']) && auth()->user()?->school_id) {
            $data['school_id'] = auth()->user()->school_id;
        }

        return $data;
    }
}
