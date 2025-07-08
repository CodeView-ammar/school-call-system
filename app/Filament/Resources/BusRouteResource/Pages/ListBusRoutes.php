<?php

namespace App\Filament\Resources\BusRouteResource\Pages;

use App\Filament\Resources\BusRouteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusRoutes extends ListRecords
{
    protected static string $resource = BusRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مسار جديد'),
        ];
    }
}
// <?php

// namespace App\Filament\Resources\BusRouteResource\Pages;

// use App\Filament\Resources\BusRouteResource;
// use Filament\Actions;
// use Filament\Resources\Pages\ListRecords;

// class ListBusRoutes extends ListRecords
// {
//     protected static string $resource = BusRouteResource::class;

//     protected function getHeaderActions(): array
//     {
//         return [
//             Actions\CreateAction::make()
//                 ->label('إنشاء مسار جديد'),
//         ];
//     }
// }
