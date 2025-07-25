<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;

Route::get('/', function () {
    return view('home');
});

// Language switching routes
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('filament.admin.locale');
