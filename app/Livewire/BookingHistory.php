<?php

namespace App\Livewire;

use Filament\Tables;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\BookingInvoice;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Concerns\InteractsWithTable;

class BookingHistory extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table)
    {
        return $table
            ->query(BookingInvoice::query())
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Reference')->searchable(),
                Tables\Columns\TextColumn::make('hotel.name')->label('Hotel'),
                // Tables\Columns\TextColumn::make('customer.firstname')->label('Customer'),
                Tables\Columns\TextColumn::make('amount')->label('Amount')->money('NGN'),
                Tables\Columns\TextColumn::make('checked_in')->label('Checked in Date')->dateTime('d M Y'),
                Tables\Columns\TextColumn::make('checked_out')->label('Checked Out Date')->dateTime('d M Y'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date Booked')->dateTime('d M Y, h:iA')
                    ->weight(FontWeight::SemiBold),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function render()
    {
        return view('livewire.booking-history');
    }
}