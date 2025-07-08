<?php

namespace App\Filament\Resources\AcademicBandWeekDayResource\Pages;

use App\Filament\Resources\AcademicBandWeekDayResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcademicBandWeekDay extends EditRecord
{
    protected static string $resource = AcademicBandWeekDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
