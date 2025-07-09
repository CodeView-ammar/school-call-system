<?php

namespace App\Filament\Resources\AcademicBandResource\Pages;

use App\Filament\Resources\AcademicBandResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademicBand extends CreateRecord
{
    protected static string $resource = AcademicBandResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
        {
            // تأكد من وجود school_id
            if (!isset($data['school_id']) && auth()->user()?->school_id) {
                $data['school_id'] = auth()->user()->school_id;
            }

            return $data;
        }
}
