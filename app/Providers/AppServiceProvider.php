<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\School;
use App\Observers\SchoolObserver;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

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
        // ضبط اللغة
        $locale = Session::get('app_locale', config('app.locale'));
        App::setLocale($locale);

        // تسجيل الـ Observer
        School::observe(SchoolObserver::class);

        // التحقق من اشتراك المدرسة عند استخدام Filament
        Filament::serving(function () {
            $user = Auth::user();
            
            if ($user && $user->school) {
                // نفترض أن لديك دالة في موديل School للتحقق من الاشتراك
                if (!$user->school->licenses()->valid()->active()->exists()) {
                    Auth::logout();
                    Notification::make()
                        ->title('اشتراك المدرسة غير مفعل')
                        ->danger() // لون الرسالة أحمر
                        ->send();
                    redirect()->to('/admin/login')->send();
                }
            }
        });
    }
}
