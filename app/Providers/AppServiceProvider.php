<?php

namespace App\Providers;

use App\Models\Room;
use App\Models\Hotel;
use App\Models\Booking;
use App\Models\CreditPayment;
use App\Models\BookingInvoice;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CacheService;
use App\Services\DatabaseOptimizationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register optimization services
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService();
        });
        
        $this->app->singleton(DatabaseOptimizationService::class, function ($app) {
            return new DatabaseOptimizationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            Schema::defaultStringLength(191);
        }

        // Register model observers for response cache invalidation
        $this->registerObservers();

        FilamentAsset::register([
            Css::make('db', __DIR__ . '/../../resources/css/dashboard.css'),
        ]);

        FilamentColor::register([
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
        ]);

        //return without wrapper
        JsonResource::withoutWrapping();
    }

    /**
     * Register observers for model events to handle cache invalidation
     */
    private function registerObservers(): void
    {
        // All observers have been removed as they weren't performing any operations
    }
}
