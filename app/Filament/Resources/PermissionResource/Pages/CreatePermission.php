<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تأكد من وجود school_id
        if (!isset($data['school_id']) && auth()->user()?->school_id) {
            $data['school_id'] = auth()->user()->school_id;
        }

        return $data;
    }
}
