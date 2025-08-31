<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale');

        if (!$locale && auth()->check() && auth()->user()->locale) {
            $locale = auth()->user()->locale;
            session(['locale' => $locale]);
        }

        if (!$locale) {
            $locale = config('app.locale', 'ar');
        }

        if (in_array($locale, ['ar', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}