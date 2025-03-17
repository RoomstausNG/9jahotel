<?php

namespace App\Livewire;

use App\Models\Hotel;
use App\Models\Amenity;
use Livewire\Component;
use App\Models\CityCopy;
use Livewire\Attributes\Layout;

class SearchResult extends Component
{
    public CityCopy $city;
    public $name = '';
    public $low = null;
    public $high = null;
    public $filterAmenities = []; // Array to store selected amenity IDs

    protected $queryString = [
        'name' => ['except' => ''],
        'low' => ['except' => null],
        'high' => ['except' => null],
        'filterAmenities' => ['except' => []],
    ];

    // Reset all filters
    public function resetFilters()
    {
        $this->name = '';
        $this->low = null;
        $this->high = null;
        $this->filterAmenities = [];
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        // Get city ID from the city model or session
        $cityId = $this->city->id ?? session('city');

        // Build the base query
        $query = Hotel::with(['city', 'amenities'])
            ->where('city_id', $cityId);

        // Apply name filter if provided
        if (!empty($this->name)) {
            $term = '%' . trim($this->name) . '%';
            $query->where('name', 'LIKE', $term);
        }

        // Apply price range filters if provided
        if (!is_null($this->low) && is_numeric($this->low)) {
            $query->where('price', '>=', $this->low);
        }

        if (!is_null($this->high) && is_numeric($this->high)) {
            $query->where('price', '<=', $this->high);
        }

        // Apply amenity filters if any are selected
        if (!empty($this->filterAmenities)) {
            // This ensures hotels have ALL the selected amenities
            foreach ($this->filterAmenities as $amenityId) {
                $query->whereHas('amenities', function ($q) use ($amenityId) {
                    $q->where('amenities.id', $amenityId);
                });
            }
        }

        // Get all hotels (without pagination)
        $hotels = $query->get();



        // Alternative method if the above doesn't work
        $hotelIds = Hotel::where('city_id', $cityId)->pluck('id');
        $amenities = Amenity::whereHas('hotels', function ($query) use ($hotelIds) {
            $query->whereKey($hotelIds);
        })->get();

        return view('livewire.search-result', [
            'hotels' => $hotels,
            'amenities' => $amenities
        ]);
    }
}
