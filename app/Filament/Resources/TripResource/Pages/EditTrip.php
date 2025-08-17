<?php

namespace App\Filament\Resources\TripResource\Pages;

use App\Filament\Resources\TripResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrip extends EditRecord
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('configure_times')
                ->label('تكوين الأوقات')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->url(fn (): string => TripResource::getUrl('configure-times', ['record' => $this->record])),
                
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'تعديل الرحلة';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم حفظ التغييرات بنجاح';
    }
}