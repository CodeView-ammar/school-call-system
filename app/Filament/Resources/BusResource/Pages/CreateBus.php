<?php

namespace App\Filament\Resources\BusResource\Pages;

use App\Filament\Resources\BusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBus extends CreateRecord
{
    protected static string $resource = BusResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
{
    if (auth()->user()?->school_id) {
        $data['school_id'] = auth()->user()->school_id;
    }
    return $data;
}
}
