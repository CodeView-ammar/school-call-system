<?php

namespace App\Filament\Resources\GateResource\Pages;

use App\Filament\Resources\GateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGates extends ListRecords
{
    protected static string $resource = GateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
