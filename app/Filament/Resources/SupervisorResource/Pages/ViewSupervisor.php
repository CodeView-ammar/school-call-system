<?php

namespace App\Filament\Resources\SupervisorResource\Pages;

use App\Filament\Resources\SupervisorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSupervisor extends ViewRecord
{
    protected static string $resource = SupervisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle_status')
                ->label(fn () => $this->record->is_active ? 'إلغاء التفعيل' : 'تفعيل')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-mark' : 'heroicon-o-check')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->action(function () {
                    $this->record->toggleStatus();
                    $this->refreshFormData(['is_active']);
                })
                ->requiresConfirmation()
                ->modalHeading(fn () => 
                    $this->record->is_active ? 'إلغاء تفعيل المساعد' : 'تفعيل المساعد'
                )
                ->modalDescription(fn () => 
                    'هل أنت متأكد من تغيير حالة المساعد: ' . $this->record->name_ar . '؟'
                ),

            Actions\EditAction::make()
                ->label('تعديل'),

            Actions\DeleteAction::make()
                ->label('حذف')
                ->before(function () {
                    // فصل جميع العلاقات قبل الحذف
                    $this->record->students()->detach();
                    $this->record->guardians()->detach();
                    $this->record->buses()->detach();
                }),
        ];
    }
}