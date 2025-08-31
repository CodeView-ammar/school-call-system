<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch($locale)
    {
        // التحقق من أن اللغة مدعومة
        if (in_array($locale, ['ar', 'en'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);

            // تحديث اللغة في قاعدة البيانات للمستخدم المسجل
            if (auth()->check()) {
                auth()->user()->update(['locale' => $locale]);
            }
        }

        return redirect()->back();
    }
}