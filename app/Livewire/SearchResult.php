<?php

namespace App\Livewire;

use App\Models\CityCopy;
use App\Models\Hotel;
use Livewire\Attributes\Layout;
use Livewire\Component;

class SearchResult extends Component
{
    public CityCopy $city;
    public $name, $low, $high;
    public $perPage = 12;


    // function mount($city = null)
    // {
    //     if ($this->city) {
    //         $this->hotels = Hotel::where('city_id', $city->id)->paginate($this->perPage);
    //     }
    // }


    public function increasePage()
    {
        $this->perPage += 12;
    }

    // public function mount($city)
    // {
    //     $this->city = $city;
    // }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        $term = trim("%" . $this->name . "%");
        $hotels = Hotel::with('city')->where('name', 'LIKE', $term)->where('city_id', $this->city->id)->paginate($this->perPage);

        if (session('city')) {
            $hotels = Hotel::with('city')->where('name', 'LIKE', $term)->where('city_id', session('city'))->paginate($this->perPage);
        }
        return view('livewire.search-result', compact(['hotels']));
    }
}