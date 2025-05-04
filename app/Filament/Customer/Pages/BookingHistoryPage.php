<?php

namespace App\Filament\Customer\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use App\Models\Booking;

class BookingHistoryPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string $view = 'filament.customer.pages.booking-history-page';
    protected static ?string $title = 'Booking History';
    protected static ?string $navigationLabel = 'Booking History';
    protected static ?string $slug = 'bookings';

    public function table(Tables\Table $table): Tables\Table
    {
        $customer = Auth::guard('customers')->user();
        return $table
            ->query(Booking::query()->where('customer_id', $customer->id))
            ->columns([
                TextColumn::make('id')->label('Booking ID'),
                TextColumn::make('hotel.name')->label('Hotel'),
                TextColumn::make('status')->label('Status'),
                TextColumn::make('created_at')->dateTime('M d, Y'),
            ])
            ->actions([
                Action::make('view')->url(fn($record) => route('filament.customer.pages.booking-view', $record)),
                Action::make('cancel')->action(fn($record) => $record->update(['status' => 'cancelled']))->requiresConfirmation()->visible(fn($record) => $record->status !== 'cancelled'),
            ]);
    }
}
