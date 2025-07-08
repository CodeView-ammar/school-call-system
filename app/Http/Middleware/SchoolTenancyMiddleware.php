<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SchoolTenancyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // إذا لم يكن هناك مستخدم مسجل، اتركه للـ middleware الأخرى
        if (!$user) {
            return $next($request);
        }
        
        // السوبر أدمن يمكنه الوصول لكل شيء
        if ($user->is_super_admin) {
            return $next($request);
        }
        
        // إذا كان المستخدم لديه مدرسة مربوطة به
        if ($user->school_id) {
            // تخزين معرف المدرسة في الجلسة للاستخدام في الاستعلامات
            session(['user_school_id' => $user->school_id]);
            session(['user_type' => $user->user_type]);
            session(['can_manage_school' => $user->can_manage_school]);
            
            return $next($request);
        }
        
        // إذا لم يكن لديه مدرسة ولا هو سوبر أدمن
        abort(403, 'غير مسموح لك بالوصول إلى هذا الجزء من النظام');
    }
}