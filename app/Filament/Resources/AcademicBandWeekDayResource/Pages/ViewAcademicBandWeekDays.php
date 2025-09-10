<?php

namespace App\Filament\Resources\AcademicBandWeekDayResource\Pages;

use App\Filament\Resources\AcademicBandWeekDayResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAcademicBandWeekDay extends ViewRecord
{
    protected static string $resource = AcademicBandWeekDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل'),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->requiresConfirmation()
                ->modalHeading('حذف الجدول')
                ->modalDescription('هل أنت متأكد من حذف هذا الجدول؟ لا يمكن التراجع عن هذا الإجراء.')
                ->modalSubmitActionLabel('نعم، احذف'),
        ];
    }
}