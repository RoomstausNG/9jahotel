<?php

namespace App\Filament\Hotel\Widgets;

use App\Models\BookingInvoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HotelStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBooking = myHotel()->bookingInvoices()->count();
        $todayBooking = myHotel()->bookingInvoices()->whereDate('created_at', today())->count();
        return [
            Stat::make('Total Bookings', $totalBooking)
                // ->description('32k increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Todays Booking', $todayBooking)
                // ->description('7% decrease')
                ->descriptionIcon('heroicon-m-arrow-trending-down'),
            Stat::make('Total Room', myHotel()->rooms()->count())
                // ->description('7% decrease')
                ->descriptionIcon('heroicon-m-arrow-trending-down'),
            Stat::make('Total Hall', myHotel()->halls()->count())
                // ->description('7% decrease')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
        ];
    }
}