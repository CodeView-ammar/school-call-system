<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // تحديد اللغة من الجلسة أو استخدام اللغة الافتراضية (العربية)
        $locale = Session::get('locale', config('app.locale', 'ar'));
        
        // التحقق من أن اللغة مدعومة
        if (in_array($locale, ['ar', 'en'])) {
            app()->setLocale($locale);
        } else {
            app()->setLocale('ar'); // الافتراضي العربية
        }

        return $next($request);
    }
}