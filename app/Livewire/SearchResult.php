<?php

namespace App\Livewire;

use App\Models\Hotel;
use App\Models\Amenity;
use Livewire\Component;
use App\Models\CityCopy;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\WithPagination; // Add this import
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;

class SearchResult extends Component
{
    use WithPagination; // Add this trait

    public CityCopy $city;
    public $power_id;
    public $name = '';
    public $low = null;
    public $high = null;
    public $filterAmenities = []; // Array to store selected amenity IDs
    public $sortBy = ''; // Property for sorting options
    public $perPage = 12; // Items per page

    protected $queryString = [
        'name' => ['except' => ''],
        'low' => ['except' => null],
        'high' => ['except' => null],
        'filterAmenities' => ['except' => []],
        'sortBy' => ['except' => ''],
        'page' => ['except' => 1], // Add page to query string
    ];

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


    // Reset all filters
    public function resetFilters()
    {
        $this->name = '';
        $this->low = null;
        $this->high = null;
        $this->filterAmenities = [];
        $this->sortBy = '';
        $this->resetPage(); // Use Livewire's resetPage method
    }

    // Removed increasePage method

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        // Get city ID from the city model or session
        $cityId = $this->city->id ?? session('city');

        // Build the base query
        $query = Hotel::with(['city', 'amenities', 'ratings'])
            ->where('city_id', $cityId);

        // Apply name filter if provided
        if (!empty($this->name)) {
            $term = '%' . trim($this->name) . '%';
            $query->where('name', 'LIKE', $term);
        }

        // Apply price range filters if provided - now using room_lowest_price directly
        if ((!is_null($this->low) && is_numeric($this->low))) {
            $query->where('room_lowest_price', '>=', $this->low);
        }

        if ((!is_null($this->high) && is_numeric($this->high))) {
            $query->where('room_lowest_price', '<=', $this->high);
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
            'class_low' => ['column' => 'star_rating', 'direction' => 'asc'],
            'price_low' => ['column' => 'room_lowest_price', 'direction' => 'asc'],
            'price_high' => ['column' => 'room_lowest_price', 'direction' => 'desc'],
        ];

        // Apply sorting based on selected option
        if (isset($sortOptions[$this->sortBy])) {
            $sort = $sortOptions[$this->sortBy];
            $query->orderBy($sort['column'], $sort['direction']);
        } elseif ($this->sortBy === 'rating_high' || $this->sortBy === 'rating_low') {
            // Handle rating sorting using the ratings relationship
            $direction = $this->sortBy === 'rating_high' ? 'desc' : 'asc';

            // Use a subquery to get the average rating for each hotel
            $query->select('hotels.*')
                ->leftJoin('ratings', 'hotels.id', '=', 'ratings.hotel_id')
                ->groupBy('hotels.id')
                ->orderBy(\DB::raw('AVG(ratings.number)'), $direction);
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

        // Get map URL (already cached in the method)

        return view('livewire.search-result', [
            'hotels' => $hotels,
            'amenities' => $amenities,
            'totalFilteredCount' => $totalFilteredCount,
        ]);
    }
}