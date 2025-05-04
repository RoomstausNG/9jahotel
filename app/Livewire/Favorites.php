<?php

namespace App\Livewire;

use Filament\Tables;
use App\Models\Hotel;
use Livewire\Component;
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
    public $favorites;

    public function mount()
    {
        $this->loadFavorites();
    }

    public function updatedCity()
    {
        $this->loadFavorites();
    }

    public function loadFavorites()
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            $this->favorites = collect();
            return;
        }
        $query = $customer->favourites()->with(['hotel.city']);
        if ($this->city) {
            $query->whereHas('hotel', function ($q) {
                $q->where('city_id', $this->city);
            });
        }
        $this->favorites = $query->get();
    }

    public function table(Table $table): Table
    {
        $customer = auth('customer')->user();
        $query = $customer ? $customer->favourites()->with(['hotel.city']) : Favourite::query()->whereNull('id');
        return $table
            ->query($query)
            ->columns([
                TextColumn::make('hotel.name')->label('Hotel')->searchable(),
                TextColumn::make('hotel.city.name')->label('City')->searchable(),
                TextColumn::make('hotel.address')->label('Address')->searchable(),
            ])
            ->filters([
                SelectFilter::make('city_id')
                    ->label('City')
                    ->options(function () use ($customer) {
                        if (!$customer) return [];
                        return $customer->favourites()->with('hotel.city')->get()->pluck('hotel.city.name', 'hotel.city.id')->unique();
                    })
                    ->query(function ($query, $value) {
                        if ($value) {
                            $query->whereHas('hotel', fn($q) => $q->where('city_id', $value));
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('unfavorite')
                    ->label('Unfavorite')
                    ->color('danger')
                    ->action(fn($record) => $this->unfavorite($record->id)),
            ]);
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
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