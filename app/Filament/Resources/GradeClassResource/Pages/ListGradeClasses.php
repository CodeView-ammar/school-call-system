<?php

namespace App\Filament\Resources\GradeClassResource\Pages;

use App\Filament\Resources\GradeClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGradeClasses extends ListRecords
{
    protected static string $resource = GradeClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
