<?php

namespace App\Filament\Resources\StudentCallResource\Pages;

use App\Filament\Resources\StudentCallResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentCall extends EditRecord
{
    protected static string $resource = StudentCallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
