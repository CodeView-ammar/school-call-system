<?php

namespace App\Filament\Resources\AcademicBandWeekDayResource\Pages;

use App\Filament\Resources\AcademicBandWeekDayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcademicBandWeekDays extends ListRecords
{
    protected static string $resource = AcademicBandWeekDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة جدولة جديدة'),
        ];
    }
}
