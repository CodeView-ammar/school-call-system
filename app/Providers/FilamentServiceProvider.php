<?php

namespace App\Providers;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
     
    Filament::serving(function () {
        Filament::registerNavigationItems([
            // NavigationItem::make()
            //     ->label('الإشعارات')
            //     ->icon('heroicon-o-bell')
            //     ->url(route('notifications')) // رابط صفحة الإشعارات
            //     ->group('النظام')
            //     ->sort(0),
        ]);
    });
    }
}
