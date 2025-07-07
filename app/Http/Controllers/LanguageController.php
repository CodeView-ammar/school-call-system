<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switch(Request $request, string $locale): JsonResponse
    {
        // Validate locale
        if (!in_array($locale, ['ar', 'en'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid locale'
            ], 400);
        }

        // Store locale in session
        Session::put('locale', $locale);
        
        // Set app locale
        App::setLocale($locale);
        
        return response()->json([
            'success' => true,
            'locale' => $locale,
            'direction' => $locale === 'ar' ? 'rtl' : 'ltr',
            'message' => $locale === 'ar' ? 'تم تغيير اللغة بنجاح' : 'Language changed successfully'
        ]);
    }
    
    /**
     * Get current language info
     */
    public function current(): JsonResponse
    {
        $locale = App::getLocale();
        
        return response()->json([
            'locale' => $locale,
            'direction' => $locale === 'ar' ? 'rtl' : 'ltr',
            'name' => $locale === 'ar' ? 'العربية' : 'English'
        ]);
    }
}