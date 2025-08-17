<?php

namespace App\Filament\Resources\TripResource\Pages;

use App\Filament\Resources\TripResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTrip extends CreateRecord
{
    protected static string $resource = TripResource::class;

    public function getTitle(): string
    {
        return 'إنشاء رحلة جديدة';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('configure-times', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الرحلة بنجاح';
    }
}