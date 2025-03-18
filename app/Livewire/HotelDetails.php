<?php

namespace App\Livewire;

use App\Models\Type;
use App\Models\Hotel;
use Livewire\Component;
use Livewire\Attributes\Layout;

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

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        return view('livewire.hotel-details');
    }
}
