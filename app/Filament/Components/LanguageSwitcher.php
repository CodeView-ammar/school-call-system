<?php

namespace App\Filament\Components;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Widget
{
    protected static string $view = 'filament.components.language-switcher';
    
    protected static ?int $sort = -3;
    
    protected static bool $isLazy = false;
    
    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
    
    public function getCurrentLocale(): string
    {
        return App::getLocale();
    }
    
    public function isRtl(): bool
    {
        return App::getLocale() === 'ar';
    }
}