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


    public function render()
    {
        $cityId = $this->city->id ?? session('city');
        $cityName = $this->city->name ?? ($cityId ? \Nnjeim\World\Models\City::find($cityId)?->name : null);

        // Default SEO values
        $seoTitle = config('app.name') . ' | Search & Save on your bookings';
        $seoDescription = 'Find and book the best hotel deals in Nigeria. Save on your accommodation with 9jahotel.';
        $seoKeywords = 'hotels, Nigeria, accommodation, booking, hotel deals, discount hotels, 9jahotel';

        $hotels = collect();
        $amenities = collect();
        $totalFilteredCount = 0;

        if ($cityId) {
            $query = Hotel::hasTypes()->forSearchResults()->with(['city', 'amenities', 'ratings'])
                ->where('city_id', $cityId);

            // Apply name filter if provided
            if (!empty($this->name)) {
                $term = '%' . trim($this->name) . '%';
                $query->where('name', 'LIKE', $term);
            }

            // Apply price range filters if provided
            if (!empty($this->low) && is_numeric($this->low)) {
                $query->where('room_lowest_price', '>=', floatval($this->low));
            }
            if (!empty($this->high) && is_numeric($this->high)) {
                $query->where('room_lowest_price', '<=', floatval($this->high));
            }

            // Apply amenity filters
            if (!empty($this->filterAmenities)) {
                foreach ($this->filterAmenities as $amenityId) {
                    $query->whereHas('amenities', function ($q) use ($amenityId) {
                        $q->where('amenities.id', $amenityId);
                    });
                }
            }

            // Sorting
            $sortOptions = [
                'class_high' => ['column' => 'star_rating', 'direction' => 'desc'],
                'price_low' => ['column' => 'room_lowest_price', 'direction' => 'asc'],
                'price_high' => ['column' => 'room_lowest_price', 'direction' => 'desc'],
                'rating_high' => ['special' => 'rating'],
            ];
            if (!empty($this->sortBy)) {
                if (isset($sortOptions[$this->sortBy])) {
                    $sort = $sortOptions[$this->sortBy];
                    if (isset($sort['special']) && $sort['special'] === 'rating') {
                        $query->select('hotels.*')
                            ->leftJoin('ratings', 'hotels.id', '=', 'ratings.hotel_id')
                            ->groupBy('hotels.id')
                            ->orderBy(\DB::raw('AVG(COALESCE(ratings.number, 0))'), 'desc');
                    } else {
                        $query->orderBy($sort['column'], $sort['direction']);
                    }
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $totalFilteredCount = $query->count();
            $hotels = $query->paginate($this->perPage);

            $amenities = Cache::remember("city_amenities_{$cityId}", 60 * 5, function () use ($cityId) {
                return Amenity::select('amenities.*')
                    ->join('amenity_hotel', 'amenities.id', '=', 'amenity_hotel.amenity_id')
                    ->join('hotels', 'amenity_hotel.hotel_id', '=', 'hotels.id')
                    ->where('hotels.city_id', $cityId)
                    ->distinct()
                    ->get();
            });

            // SEO for city page
            if ($cityName) {
                $seoTitle = 'Hotels in ' . $cityName . ' | ' . config('app.name');
                $seoDescription = "Find and book from $totalFilteredCount hotels in $cityName. Save on your accommodation with 9jahotel.";
                $seoKeywords = "hotels, $cityName, accommodation, booking, hotel deals, discount hotels, 9jahotel";
            }
        }

        return view('livewire.search-result', [
            'hotels' => $hotels,
            'amenities' => $amenities,
            'totalFilteredCount' => $totalFilteredCount,
        ])->layout('components.layouts.frontend', [
            'title' => $seoTitle,
            'description' => $seoDescription,
            'keywords' => $seoKeywords,
        ]);
    }

    // Add hotel to favorites
    public function addToFavorite($hotelId)
    {
        $customer = auth()->user();

        if (!$customer) {
            return Notification::make()
                ->title('You need to login to add hotel to favorite')
                ->duration(7000)
                ->warning()
                ->send();
        }

        // Check if the hotel is already a favorite
        if ($customer->favourites()->where('hotel_id', $hotelId)->exists()) {
            return Notification::make()
                ->title('Hotel is already in your favorites')
                ->duration(7000)
                ->info()
                ->send();
        }

        // Add to favorites
        $customer->favourites()->create(['hotel_id' => $hotelId]);

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
}