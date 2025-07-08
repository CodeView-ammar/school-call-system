<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // السماح للأدمن العام بالوصول لجميع البيانات
        if ($user && $user->hasRole('super_admin')) {
            return $next($request);
        }

        // التحقق من وجود school_id في الطلب
        $schoolId = $request->route('school_id') ?? $request->get('school_id');
        
        if ($schoolId && $user) {
            // التحقق من صلاحية الوصول للمدرسة
            if (!$user->canAccessSchool($schoolId)) {
                abort(403, 'ليس لديك صلاحية للوصول لهذه المدرسة');
            }
        }

        return $next($request);
    }
}