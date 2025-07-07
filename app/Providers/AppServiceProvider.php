<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\School;
use App\Observers\SchoolObserver;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = Session::get('app_locale', config('app.locale'));
        App::setLocale($locale);
        // تسجيل الـ Observer
        School::observe(\App\Observers\SchoolObserver::class);
    }
}
