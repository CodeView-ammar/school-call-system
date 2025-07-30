<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;

Route::get('/', function () {
    return view('home');
});

// Language switching routes
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('filament.admin.locale');


Route::get('/admin/notifications', function () {
    return view('filament.pages.notifications'); // مثال مؤقت
})->name('notifications');
