<?php

namespace App\Filament\Resources\StudentCallResource\Pages;

use App\Filament\Resources\StudentCallResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentCalls extends ListRecords
{
    protected static string $resource = StudentCallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
