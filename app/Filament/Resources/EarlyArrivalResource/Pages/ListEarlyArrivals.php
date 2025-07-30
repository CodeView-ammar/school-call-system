<?php

namespace App\Filament\Resources\EarlyArrivalResource\Pages;

use App\Filament\Resources\EarlyArrivalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEarlyArrivals extends ListRecords
{
    protected static string $resource = EarlyArrivalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
