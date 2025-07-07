<?php

namespace App\Filament\Resources\GradeClassResource\Pages;

use App\Filament\Resources\GradeClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeClass extends EditRecord
{
    protected static string $resource = GradeClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
