<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use App\Http\Middleware\HotelMiddleware;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class HotelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('hotel')
            ->path('hotel')
            ->login()
            // ->spa()
            ->passwordReset()
            ->unsavedChangesAlerts()
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(MaxWidth::Full)
            ->colors([
                'primary' => Color::Amber,
                'danger' => Color::rgb('rgb(255, 0, 0)'),
                'available' => Color::hex('#ddd'),       // white
                'pending' => Color::hex('#ffcc00'),      // yellow
                'unpaid' => Color::hex('#ffcc00'),       // yellow
                'booked' => Color::hex('#008000'),       // green
                'paid' => Color::hex('#008000'),         // green
                'occupied' => Color::hex('#FF0000'),     // red
                'late' => Color::hex('#FFA500'),         // orange
                'dirty' => Color::hex('#A52A2A'),        // brown
                'repair' => Color::hex('#000000'),       // black
                'Available' => Color::hex('#ddd'),       // white
                'Pending' => Color::hex('#ffcc00'),      // yellow
                'Unpaid' => Color::hex('#ffcc00'),       // yellow
                'Paid' => Color::hex('#008000'),         // green
                'Occupied' => Color::hex('#FF0000'),     // red
                'Late' => Color::hex('#aa3300'),         // dark orange
                'Dirty' => Color::hex('#A52A2A'),        // brown
                'Repair' => Color::hex('#000000'),       // black
                'income' => Color::hex('#05cc0a'),       // green
                'expenditure' => Color::hex('#ee0016'),  // red
            ])
            ->discoverResources(in: app_path('Filament/Hotel/Resources'), for: 'App\\Filament\\Hotel\\Resources')
            ->discoverPages(in: app_path('Filament/Hotel/Pages'), for: 'App\\Filament\\Hotel\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Hotel/Widgets'), for: 'App\\Filament\\Hotel\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])->brandName(fn() => auth()->check() ? myHotel()->name : env('APP_NAME'))
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
                'hotel'
            ]);
    }
}
