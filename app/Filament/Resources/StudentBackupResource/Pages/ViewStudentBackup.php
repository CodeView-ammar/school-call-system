<?php

namespace App\Filament\Resources\StudentBackupResource\Pages;

use App\Filament\Resources\StudentBackupResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentBackup extends ViewRecord
{
    protected static string $resource = StudentBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}