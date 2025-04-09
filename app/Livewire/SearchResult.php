<?php

namespace App\Livewire;

use App\Models\Hotel;
use App\Models\Amenity;
use Livewire\Component;
use App\Models\CityCopy;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class SearchResult extends Component
{
    use WithPagination;

    public CityCopy $city;
    public $power_id;
    public $name = '';
    public $low = null;
    public $high = null;
    public $filterAmenities = []; // Array to store selected amenity IDs
    public $sortBy = ''; // Property for sorting options
    public $perPage = 12; // Items per page
    // Remove the $page property as it's handled by WithPagination trait

    protected $queryString = [
        'name' => ['except' => ''],
        'low' => ['except' => null],
        'high' => ['except' => null],
        'filterAmenities' => ['except' => []],
        'sortBy' => ['except' => ''],
        'page' => ['except' => 1], // Keep this in queryString but don't define it as a property
    ];

    // Add listeners for specific events
    protected $listeners = [
        'sortHotels' => 'setSortBy',
    ];

    public function mount()
    {
        // Initialize any required properties
        if (!isset($this->city) && session()->has('city')) {
            // If city is not provided but exists in session, retrieve it
            $cityId = session('city');
            $this->city = CityCopy::find($cityId);
        }
    }

    // Method to handle sorting explicitly
    public function setSortBy($value)
    {
        $this->sortBy = $value;
        $this->resetPage();
    }

    public function addToFavorite($hotelId)
    {
        $customer = auth()->guard('customers')->user();

        if (!$customer) {
            return Notification::make()
                ->title('You need to login to add hotel to favorite')
                ->duration(7000)
                ->warning()
                ->send();
        }

        // Check if the hotel is already a favorite
        if ($customer->favorites()->where('hotel_id', $hotelId)->exists()) {
            return Notification::make()
                ->title('Hotel is already in your favorites')
                ->duration(7000)
                ->info()
                ->send();
        }

        // Add to favorites
        $customer->favorites()->create(['hotel_id' => $hotelId]);

        return Notification::make()
            ->title('Hotel added to favorite successfully')
            ->duration(7000)
            ->success()
            ->send();
    }

    // Apply price filter explicitly
    public function applyPriceFilter()
    {
        $this->resetPage();
    }

    // Reset all filters
    public function resetFilters()
    {
        $this->name = '';
        $this->low = null;
        $this->high = null;
        $this->filterAmenities = [];
        $this->sortBy = '';
        $this->resetPage(); // Use Livewire's resetPage method

        // Dispatch an Alpine.js event to clear input fields
        $this->dispatch('reset-filters');
    }

    // Methods to reset pagination when filters change
    public function updatedName()
    {
        $this->resetPage();
    }

    public function updatedFilterAmenities()
    {
        $this->resetPage();
    }

    public function updatedSortBy($value)
    {
        // Reset pagination when sort changes
        $this->resetPage();
        
        // Ensure only one sort is active at a time
        // The $value parameter already contains the new sort value
        // No need to do anything else as Livewire will update the property
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        // Get city ID from the city model or session
        $cityId = $this->city->id ?? session('city');

        if (!$cityId) {
            // Handle case where no city is selected
            return view('livewire.search-result', [
                'hotels' => collect(),
                'amenities' => collect(),
                'totalFilteredCount' => 0,
            ]);
        }

        // Build the base query
        $query = Hotel::hasTypes()->forSearchResults()->with(['city', 'amenities', 'ratings'])
            ->where('city_id', $cityId);

        // Apply name filter if provided
        if (!empty($this->name)) {
            $term = '%' . trim($this->name) . '%';
            $query->where('name', 'LIKE', $term);
        }

        // Apply price range filters if provided - now using room_lowest_price directly
        if (!empty($this->low) && is_numeric($this->low)) {
            $query->where('room_lowest_price', '>=', floatval($this->low));
        }

        if (!empty($this->high) && is_numeric($this->high)) {
            $query->where('room_lowest_price', '<=', floatval($this->high));
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

        // Define sorting options as an associative array
        $sortOptions = [
            'class_high' => ['column' => 'star_rating', 'direction' => 'desc'],
            'price_low' => ['column' => 'room_lowest_price', 'direction' => 'asc'],
            'price_high' => ['column' => 'room_lowest_price', 'direction' => 'desc'],
            'rating_high' => ['special' => 'rating'],
        ];

        // Apply sorting based on selected option
        if (!empty($this->sortBy)) {
            if (isset($sortOptions[$this->sortBy])) {
                $sort = $sortOptions[$this->sortBy];
                
                if (isset($sort['special']) && $sort['special'] === 'rating') {
                    // Handle rating sorting using the ratings relationship
                    $query->select('hotels.*')
                        ->leftJoin('ratings', 'hotels.id', '=', 'ratings.hotel_id')
                        ->groupBy('hotels.id')
                        ->orderBy(\DB::raw('AVG(COALESCE(ratings.number, 0))'), 'desc');
                } else {
                    // Standard column sorting
                    $query->orderBy($sort['column'], $sort['direction']);
                }
            }
        } else {
            // Default sorting (recommended)
            $query->orderBy('created_at', 'desc');
        }

        // Get the total count of filtered hotels
        $totalFilteredCount = $query->count();

        // Use paginate instead of limit and get
        $hotels = $query->paginate($this->perPage);

        // Get amenities for the city (cached to avoid repeated queries)
        $amenities = Cache::remember("city_amenities_{$cityId}", 60 * 5, function () use ($cityId) {
            return Amenity::select('amenities.*')
                ->join('amenity_hotel', 'amenities.id', '=', 'amenity_hotel.amenity_id')
                ->join('hotels', 'amenity_hotel.hotel_id', '=', 'hotels.id')
                ->where('hotels.city_id', $cityId)
                ->distinct()
                ->get();
        });

        return view('livewire.search-result', [
            'hotels' => $hotels,
            'amenities' => $amenities,
            'totalFilteredCount' => $totalFilteredCount,
        ]);
    }
}