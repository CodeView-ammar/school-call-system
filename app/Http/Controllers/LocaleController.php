<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch($locale)
    {
        // التحقق من أن اللغة مدعومة
        if (in_array($locale, ['ar', 'en'])) {
            Session::put('locale', $locale);
            app()->setLocale($locale);
        }

        return redirect()->back();
    }
}