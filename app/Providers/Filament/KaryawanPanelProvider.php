<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class KaryawanPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('karyawan')
            ->path('karyawan')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Absensi')
                    ->icon('heroicon-o-clock'),
                NavigationGroup::make()
                    ->label('Jadwal')
                    ->icon('heroicon-o-calendar-days'),
            ])
            ->discoverResources(in: app_path('Filament/Karyawan/Resources'), for: 'App\\Filament\\Karyawan\\Resources')
            ->discoverPages(in: app_path('Filament/Karyawan/Pages'), for: 'App\\Filament\\Karyawan\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Karyawan/Widgets'), for: 'App\\Filament\\Karyawan\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web')
            ->brandName('LRP - Karyawan')
            ->favicon(asset('favicon.ico'))
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                'panels::body.end',
                fn (): string => view('filament.karyawan.attendance-scripts')->render()
            );
    }
}
