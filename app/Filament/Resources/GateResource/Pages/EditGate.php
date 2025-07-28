<?php

namespace App\Filament\Resources\GateResource\Pages;

use App\Filament\Resources\GateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGate extends EditRecord
{
    protected static string $resource = GateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
