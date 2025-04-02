<?php

namespace App\Livewire;

use App\Models\CityCopy;
use Livewire\Component;
use Livewire\Features\SupportPagination\WithoutUrlPagination;
use Livewire\WithPagination;

class HotelCities extends Component
{
    use WithPagination, WithoutUrlPagination;
    public function render()
    {
        $cities = CityCopy::with('state:id,name')->withCount(['hotels', 'ratings', 'bookings'])->has('hotels')->paginate(24);
        return view('livewire.hotel-cities', compact(['cities']));
    }
}