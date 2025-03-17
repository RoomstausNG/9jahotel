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
    public $perPage = 12;

    // Mount function to initialize component
    public function mount()
    {
        // Initialize filterAmenities as empty array
        $this->filterAmenities = [];
    }

    // Function to load more hotels
    public function increasePage()
    {
        $this->perPage += 12;
    }

    // Reset all filters
    public function resetFilters()
    {
        $this->name = '';
        $this->low = null;
        $this->high = null;
        $this->filterAmenities = [];
    }

    // This will run when any filter is updated
    public function updatedFilterAmenities()
    {
        // Reset pagination when filters change
        $this->perPage = 12;
    }

    // Update price filters
    public function updatedLow()
    {
        $this->perPage = 12;
    }

    public function updatedHigh()
    {
        $this->perPage = 12;
    }

    public function updatedName()
    {
        $this->perPage = 12;
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
            foreach ($this->filterAmenities as $amenityId) {
                $query->whereHas('amenities', function ($q) use ($amenityId) {
                    $q->where('amenities.id', $amenityId);
                });
            }
        }

        // Get the filtered hotels
        $hotels = $query->paginate($this->perPage);

        // Get all amenities related to the current city's hotels for the filter sidebar
        // This correctly handles the many-to-many relationship
        $amenities = Amenity::whereHas('hotels', function ($query) use ($cityId) {
            $query->where('city_id', $cityId);
        })->get();

        return view('livewire.search-result', compact(['hotels', 'amenities']));
    }
}
