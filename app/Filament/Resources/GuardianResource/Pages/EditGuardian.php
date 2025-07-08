<?php

namespace App\Filament\Resources\GuardianResource\Pages;

use App\Filament\Resources\GuardianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use App\Filament\Resources\GuardianResource\RelationManagers\StudentsRelationManager;

class EditGuardian extends EditRecord
{
    protected static string $resource = GuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف ولي الأمر')
                ->requiresConfirmation()
                ->modalHeading('حذف ولي الأمر')
                ->modalDescription('هل أنت متأكد من حذف ولي الأمر؟')
                ->modalSubmitActionLabel('حذف')
                ->modalCancelActionLabel('إلغاء'),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            StudentsRelationManager::class,
        ];
    }
}
