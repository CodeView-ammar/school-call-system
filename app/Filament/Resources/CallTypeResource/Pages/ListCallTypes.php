<?php

namespace App\Filament\Resources\CallTypeResource\Pages;

use App\Filament\Resources\CallTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCallTypes extends ListRecords
{
    protected static string $resource = CallTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
