<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Support\Facades\FilamentView;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\LocaleMiddleware;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Smart Call - نظام النداء الذكي')
            ->favicon('/favicon.ico')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\AttendanceChart::class,
                \App\Filament\Widgets\LatestSchoolsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                LocaleMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                'school.tenancy',
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => '<script>
                    document.addEventListener("alpine:init", () => {
                        Alpine.store("sidebar", {
                            isOpen: true,
                            toggle() { this.isOpen = !this.isOpen; },
                            close() { this.isOpen = false; },
                            open() { this.isOpen = true; },
                            groupIsCollapsed(group) { return false; },
                            toggleGroup(group) { }
                        });
                        Alpine.store("theme", "system");
                    });
                    
                    // Set initial direction based on locale
                    const locale = "' . app()->getLocale() . '";
                    document.documentElement.dir = locale === "ar" ? "rtl" : "ltr";
                    document.documentElement.lang = locale;
                </script>
                <style>
                    @import url("https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap");
                    
                    // [dir="rtl"] body,
                    // [dir="rtl"] .fi-sidebar,
                    // [dir="rtl"] .fi-main {
                    //     font-family: "Tajawal", "Inter", ui-sans-serif, system-ui, sans-serif;
                    // }
                    
                    // [dir="rtl"] .fi-sidebar {
                    //     right: 0;
                    //     left: auto;
                    // }
                    
                    // [dir="rtl"] .fi-main {
                    //     margin-right: var(--sidebar-width, 15rem);
                    //     margin-left: 0;
                    // }
                    
                    // [dir="rtl"] .fi-header {
                    //     padding-right: var(--sidebar-width, 15rem);
                    //     padding-left: 1rem;
                    // }
                    
                    // [dir="rtl"] .fi-sidebar-nav-item-icon {
                    //     margin-left: 0.75rem;
                    //     margin-right: 0;
                    // }
                    
                    // [dir="rtl"] .fi-sidebar-group-items {
                    //     text-align: right;
                    // }
                </style>'
            )
            ->renderHook(
                'panels::topbar.end',
                fn (): string => view('filament.components.language-switcher')->render()
            );
    }
}
