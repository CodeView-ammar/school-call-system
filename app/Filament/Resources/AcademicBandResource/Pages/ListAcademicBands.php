<?php

namespace App\Filament\Resources\AcademicBandResource\Pages;

use App\Filament\Resources\AcademicBandResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcademicBands extends ListRecords
{
    protected static string $resource = AcademicBandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
