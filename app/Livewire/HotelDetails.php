<?php

namespace App\Livewire;

use App\Models\Hotel;
use App\Models\Type;
use Livewire\Component;

class HotelDetails extends Component
{
    public Hotel $hotel;
    public $category;

    public $review = false;
    public $modal = false;

    public function selectRoom(Type $category)
    {
        $this->category = $category;
        // dd($this->category);
        $this->modal = true;
    }

    public function render()
    {
        return view('livewire.hotel-details');
    }
}