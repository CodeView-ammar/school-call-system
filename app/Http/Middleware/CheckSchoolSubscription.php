<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CheckSchoolSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // إذا لم يسجل الدخول
        if (!$user) {
            return $next($request);
        }

        // تحقق من وجود مدرسة
        if (!$user->school || !$user->school->licenses()->valid()->active()->exists()) {

            // الطلب من لوحة Filament
            if ($request->is('admin/*')) {
                Notification::make()
                    ->title('اشتراك المدرسة غير مفعل')
                    ->danger()
                    ->send();

                // إعادة التوجيه لصفحة تسجيل الدخول
                return redirect()->route('filament.auth.login');
            }

            // الطلب من API
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'اشتراك المدرسة غير مفعل',
                ], 403);
            }

            // أي طلب آخر
            return redirect()->back()->withErrors(['message' => 'اشتراك المدرسة غير مفعل']);
        }

        return $next($request);
    }
}
