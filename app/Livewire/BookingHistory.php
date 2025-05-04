<?php

namespace App\Livewire;

use App\Models\BookingInvoice;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Component;

class BookingHistory extends Component implements HasTable
{
  use InteractsWithTable;

  public function table(Tables\Table $table): Tables\Table
  {
    return $table
      ->query(BookingInvoice::query())
      ->columns([
        Tables\Columns\TextColumn::make('reference')->label('Reference')->searchable(),
        Tables\Columns\TextColumn::make('customer.firstname')->label('Customer'),
        Tables\Columns\TextColumn::make('amount')->label('Amount')->money('NGN'),
        Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d M Y, H:i'),
      ])
      ->defaultSort('created_at', 'desc');
  }

  public function render()
  {
    return view('livewire.booking-history');
  }
}
