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
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Widgets;
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
            // ->locale(fn() => app()->getLocale())
            ->default()
            ->id('admin')
            ->path('/admin')
            ->login()
            ->brandName('Smart Call - نظام النداء الذكي')
            ->favicon('/favicon.ico')
            ->font('tajawal', 'https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap')
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Red,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
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
            ->navigationGroups([
                NavigationGroup::make()->label('نظام النداء الذكي'),
                NavigationGroup::make()->label('المحتوى'),
                NavigationGroup::make()->label('الإشعارات'),
                NavigationGroup::make()->label('الإعدادات'),
            ])
            ->navigationItems([
                // مثال على عنصر تنقل مع صلاحية
                NavigationItem::make('roles')
                    ->label('المجموعات والصلاحيات')
                    ->url('/admin/shield/roles')
                    ->icon('heroicon-o-users')
                    ->group('الإعدادات')
                    ->hidden(fn() => !auth()->user()->can('viewAny', \Spatie\Permission\Models\Role::class))
                    ->sort(5),
                // يمكنك إضافة عناصر أخرى هنا بنفس الطريقة
            ])
            ->plugins([
                // أضف البلجنز التي تحتاجها هنا
                // \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                // بلجن التبديل بين اللغات
                // \BezhanSalleh\FilamentLanguageSwitch\FilamentLanguageSwitchPlugin::make(),
            ])
            ->databaseNotifications() // **هنا تفعيل نظام الإشعارات**
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
                // أضف Middleware إضافي إذا كان لديك، مثل tenancy مثلاً
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => '
                    <script>
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

                        const locale = "' . app()->getLocale() . '";
                        document.documentElement.dir = locale === "ar" ? "rtl" : "ltr";
                        document.documentElement.lang = locale;
                    </script>
                    <style>
                        @import url("https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap");
                    </style>'
            )
            ->renderHook(
                'panels::topbar.end',
                fn (): string => view('filament.components.language-switcher')->render()
            )
            ->renderHook(
                'panels::topbar.start',
                fn (): string => view('filament.components.user-status')->render()
            );

    }
}