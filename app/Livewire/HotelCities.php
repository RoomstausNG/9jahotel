<?php

namespace App\Livewire;

use App\Models\CityCopy;
use Livewire\Component;
use Livewire\WithPagination;

class HotelCities extends Component
{
    use WithPagination;
    public function render()
    {
        $cities = CityCopy::has('hotels')->paginate(48);
        return view('livewire.hotel-cities', compact(['cities']));
    }
}