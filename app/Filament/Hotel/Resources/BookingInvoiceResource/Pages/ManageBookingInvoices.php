<?php

namespace App\Filament\Hotel\Resources\BookingInvoiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Hotel\Resources\BookingInvoiceResource;

class ManageBookingInvoices extends ManageRecords
{
    protected static string $resource = BookingInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        // return [
        //     Actions\CreateAction::make(),
        // ];
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make("All Bookings")
                ->badge(myHotel()->bookingInvoices()->count()),
            'today' => Tab::make("Today's Bookings")
                // ->tooltip(today())
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('created_at', today()))
                ->badge(myHotel()->bookingInvoices()->whereDate('created_at', today())->count()),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'today';
    }
}