<?php

namespace App\Filament\Resources\StudentBackupResource\Pages;

use App\Filament\Resources\StudentBackupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentBackup extends EditRecord
{
    protected static string $resource = StudentBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // منع تعديل الحقول الحساسة
        unset($data['file_path'], $data['file_size'], $data['students_count']);
        return $data;
    }
}