<?php

namespace App\Filament\Resources\CallTypeResource\Pages;

use App\Filament\Resources\CallTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCallType extends EditRecord
{
    protected static string $resource = CallTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
