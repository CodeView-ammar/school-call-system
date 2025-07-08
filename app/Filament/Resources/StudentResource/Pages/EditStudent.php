<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // التأكد من تحميل البيانات بشكل صحيح
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // التأكد من وجود school_id
        if (!isset($data['school_id']) || empty($data['school_id'])) {
            $data['school_id'] = auth()->user()->school_id;
        }

        return $data;
    }
}
