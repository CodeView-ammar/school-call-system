<?php

namespace App\Filament\Resources\EarlyArrivalResource\Pages;

use App\Filament\Resources\EarlyArrivalResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EarlyArrivalCreatedNotification;

class CreateEarlyArrival extends CreateRecord
{
    protected static string $resource = EarlyArrivalResource::class;

    protected function afterCreate(): void
    {
        parent::afterCreate();

        $user = auth()->user();

        if ($user) {
            Notification::send($user, new EarlyArrivalCreatedNotification($this->record));
        }
    }
}
