<?php

namespace App\Filament\Resources\AcademicBandResource\Pages;

use App\Filament\Resources\AcademicBandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcademicBand extends EditRecord
{
    protected static string $resource = AcademicBandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
