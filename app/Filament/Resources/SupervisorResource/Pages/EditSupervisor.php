<?php

namespace App\Filament\Resources\SupervisorResource\Pages;

use App\Filament\Resources\SupervisorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSupervisor extends EditRecord
{
    protected static string $resource = SupervisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض'),

            Actions\Action::make('toggle_status')
                ->label(fn () => $this->record->is_active ? 'إلغاء التفعيل' : 'تفعيل')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-mark' : 'heroicon-o-check')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->action(function () {
                    $this->record->toggleStatus();
                    $this->refreshFormData(['is_active']);
                })
                ->requiresConfirmation(),

            Actions\DeleteAction::make()
                ->label('حذف')
                ->before(function () {
                    $this->record->students()->detach();
                    $this->record->guardians()->detach();
                    $this->record->buses()->detach();
                }),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تم تحديث المساعد بنجاح')
            ->body('تم حفظ التغييرات على بيانات المساعد.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}