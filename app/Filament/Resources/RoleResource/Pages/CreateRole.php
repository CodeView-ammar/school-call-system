<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
 protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تأكد من وجود school_id
        if (!isset($data['school_id']) && auth()->user()?->school_id) {
            $data['school_id'] = auth()->user()->school_id;
        }

        return $data;
    }
}
