<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Hotel;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total = Hotel::count();
        $nineja = Hotel::where('type', '9jahotel')->count();
        $active = Hotel::where('status', 'active')->count();
        $customers = Customer::distinct('phone')->count();
        return [
            Stat::make('Total Hotels', $total),
            Stat::make('9jahotels Only', $nineja),
            Stat::make('Both', $total - $nineja),
            Stat::make('Active Hotels', $active),
            Stat::make('Inactive Hotels', $total - $active),
            Stat::make('Total Customers', $customers),
        ];
    }
}
