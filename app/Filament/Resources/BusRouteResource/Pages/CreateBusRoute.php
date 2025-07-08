<?php

namespace App\Filament\Resources\BusRouteResource\Pages;

use App\Filament\Resources\BusRouteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBusRoute extends CreateRecord
{
    protected static string $resource = BusRouteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // إضافة school_id إذا لم يكن موجوداً
        if (!isset($data['school_id']) && auth()->user()->school_id) {
            $data['school_id'] = auth()->user()->school_id;
        }

        return $data;
    }
}
// <?php

// namespace App\Filament\Resources\BusRouteResource\Pages;

// use App\Filament\Resources\BusRouteResource;
// use Filament\Resources\Pages\CreateRecord;

// class CreateBusRoute extends CreateRecord
// {
//     protected static string $resource = BusRouteResource::class;
    
//     protected function getRedirectUrl(): string
//     {
//         return $this->getResource()::getUrl('index');
//     }
// }
