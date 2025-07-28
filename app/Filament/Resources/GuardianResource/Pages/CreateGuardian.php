<?php

namespace App\Filament\Resources\GuardianResource\Pages;

use App\Filament\Resources\GuardianResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
class CreateGuardian extends CreateRecord
{
    protected static string $resource = GuardianResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم إنشاء ولي الأمر بنجاح')
            ->body('تم إضافة ولي الأمر والطلاب المرتبطين به بنجاح')
            ->icon('heroicon-o-check-circle');
    }
    protected function handleRecordCreation(array $data): Model
    {
        return GuardianResource::handleRecordCreation($data);
    }
}
