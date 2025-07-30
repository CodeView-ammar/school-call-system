<?php

namespace App\Filament\Resources\MorningStudentCallResource\Pages;

use App\Filament\Resources\MorningStudentCallResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMorningStudentCall extends EditRecord
{
    protected static string $resource = MorningStudentCallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
