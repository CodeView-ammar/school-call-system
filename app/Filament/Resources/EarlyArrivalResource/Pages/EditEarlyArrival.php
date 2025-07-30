<?php

namespace App\Filament\Resources\EarlyArrivalResource\Pages;

use App\Filament\Resources\EarlyArrivalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEarlyArrival extends EditRecord
{
    protected static string $resource = EarlyArrivalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
