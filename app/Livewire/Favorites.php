<?php

namespace App\Livewire;

use Filament\Tables;
use App\Models\Hotel;
use Livewire\Component;
use App\Models\CityCopy;
use App\Models\Favourite;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Favorites extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $city = '';
    public $favourites;

    public function mount()
    {
        // $this->loadFavorites();
    }

    public function table(Table $table): Table
    {
        $customer = auth('customers')->user();
        $query =  Hotel::with('city')->whereHas('favouritedByCustomers', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        });

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('name')->label('Hotel')->searchable(),
                TextColumn::make('city.name')->label('City')->searchable(),
                TextColumn::make('address')->label('Address')->searchable(),
            ])
            ->filters([
                SelectFilter::make('city_id')
                    ->label('City')
                    ->options(fn() => CityCopy::has('hotels')->get()->toArray()),
            ])
            ->actions([
                Tables\Actions\Action::make('unfavorite')
                    ->label('Unfavorite')
                    ->color('danger')
                    ->action(fn($record) => $this->unfavorite($record->id)),
            ]);
    }

    public function unfavorite($favoriteId)
    {
        $favorite = Favourite::find($favoriteId);
        if ($favorite) {
            $favorite->delete();
        }
    }

    public function render()
    {
        return view('livewire.favorites');
    }
}