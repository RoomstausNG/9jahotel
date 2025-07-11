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
use Illuminate\Pagination\LengthAwarePaginator;

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

    // SEO properties for layout
    public $title;
    public $description;
    public $keywords;
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


    #[Layout('components.layouts.frontend')]
    public function render()
    {
        $cityId = $this->city->id ?? session('city');
        $cityName = $this->city->name ?? ($cityId ? CityCopy::find($cityId)?->name : null);

        // Set default SEO values
        $this->setSeoDefaults();

        $hotels = collect();
        $amenities = collect();
        $totalFilteredCount = 0;

        if ($cityId) {
            // Get optimized results with single query
            $results = $this->getOptimizedHotelsQuery($cityId);
            $hotels = $results['hotels'];
            $totalFilteredCount = $results['total'];

            // Get amenities with efficient caching
            $amenities = $this->getCityAmenities($cityId);

            // Set city-specific SEO
            if ($cityName) {
                $this->setCitySeo($cityName, $totalFilteredCount);
            }
        }

        return view('livewire.search-result', [
            'hotels' => $hotels,
            'amenities' => $amenities,
            'totalFilteredCount' => $totalFilteredCount,
        ]);
    }

    /**
     * Get optimized hotels query with single database call
     */
    private function getOptimizedHotelsQuery(int $cityId): array
    {
        // Build base query with optimized joins and eager loading
        $query = Hotel::query()
            ->select([
                'hotels.*',
                DB::raw('COALESCE(AVG(ratings.number), 0) as avg_rating'),
                DB::raw('COUNT(ratings.id) as rating_count')
            ])
            ->leftJoin('ratings', 'hotels.id', '=', 'ratings.hotel_id')
            ->with(['city:id,name', 'amenities:id,name'])
            ->where('hotels.city_id', $cityId)
            ->whereHas('roomCategory') // Only hotels with room types
            ->groupBy('hotels.id');

        // Apply filters efficiently
        $this->applySearchFilters($query);
        $this->applyPriceFilters($query);
        $this->applyAmenityFilters($query);

        // Apply sorting
        $this->applySorting($query);

        // Get total count efficiently
        $totalQuery = clone $query;
        $total = $totalQuery->distinct('hotels.id')->count('hotels.id');

        // Get paginated results
        $hotels = $query->paginate($this->perPage);

        return [
            'hotels' => $hotels,
            'total' => $total
        ];
    }

    /**
     * Apply search filters efficiently
     */
    private function applySearchFilters($query): void
    {
        if (!empty($this->name)) {
            $searchTerm = '%' . trim($this->name) . '%';
            $query->where('hotels.name', 'LIKE', $searchTerm);
        }
    }

    /**
     * Apply price range filters
     */
    private function applyPriceFilters($query): void
    {
        if (!empty($this->low) && is_numeric($this->low)) {
            $query->where('hotels.room_lowest_price', '>=', floatval($this->low));
        }
        
        if (!empty($this->high) && is_numeric($this->high)) {
            $query->where('hotels.room_lowest_price', '<=', floatval($this->high));
        }
    }

    /**
     * Apply amenity filters using optimized EXISTS queries
     */
    private function applyAmenityFilters($query): void
    {
        if (!empty($this->filterAmenities)) {
            $amenityIds = array_map('intval', $this->filterAmenities);
            
            // Use EXISTS for better performance instead of multiple whereHas
            $query->where(function($q) use ($amenityIds) {
                foreach ($amenityIds as $amenityId) {
                    $q->whereExists(function($subQuery) use ($amenityId) {
                        $subQuery->select(DB::raw(1))
                            ->from('amenity_hotel')
                            ->where('amenity_hotel.hotel_id', '=', DB::raw('hotels.id'))
                            ->where('amenity_hotel.amenity_id', '=', $amenityId);
                    });
                }
            });
        }
    }

    /**
     * Apply sorting with optimized queries
     */
    private function applySorting($query): void
    {
        if (empty($this->sortBy)) {
            $query->orderBy('hotels.created_at', 'desc');
            return;
        }

        switch ($this->sortBy) {
            case 'class_high':
                $query->orderBy('hotels.star_rating', 'desc');
                break;
            case 'price_low':
                $query->orderBy('hotels.room_lowest_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('hotels.room_lowest_price', 'desc');
                break;
            case 'rating_high':
                // Use pre-calculated average from the select
                $query->orderBy('avg_rating', 'desc')
                      ->orderBy('rating_count', 'desc');
                break;
            default:
                $query->orderBy('hotels.created_at', 'desc');
        }
    }

    /**
     * Get city amenities with efficient caching
     */
    private function getCityAmenities(int $cityId)
    {
        $cacheKey = "city_amenities_{$cityId}";
        
        return Cache::remember($cacheKey, 3600, function() use ($cityId) {
            return Amenity::select('amenities.id', 'amenities.name')
                ->join('amenity_hotel', 'amenities.id', '=', 'amenity_hotel.amenity_id')
                ->join('hotels', 'amenity_hotel.hotel_id', '=', 'hotels.id')
                ->where('hotels.city_id', $cityId)
                ->whereHas('hotels.roomCategory') // Only from hotels with rooms
                ->distinct()
                ->orderBy('amenities.name')
                ->get();
        });
    }

    /**
     * Set default SEO values
     */
    private function setSeoDefaults(): void
    {
        $this->title = config('app.name') . ' | Search & Save on your bookings';
        $this->description = 'Find and book the best hotel deals in Nigeria. Save on your accommodation with 9jahotel.';
        $this->keywords = 'hotels, Nigeria, accommodation, booking, hotel deals, discount hotels, 9jahotel';
    }

    /**
     * Set city-specific SEO values
     */
    private function setCitySeo(string $cityName, int $totalCount): void
    {
        $this->title = "Hotels in {$cityName} | " . config('app.name');
        $this->description = "Find and book from {$totalCount} hotels in {$cityName}. Save on your accommodation with 9jahotel.";
        $this->keywords = "hotels, {$cityName}, accommodation, booking, hotel deals, discount hotels, 9jahotel";
    }

    // Add hotel to favorites - Optimized version
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

        // Use firstOrCreate for better performance and avoiding race conditions
        $favourite = $customer->favourites()->firstOrCreate(['hotel_id' => $hotelId]);

        if ($favourite->wasRecentlyCreated) {
            return Notification::make()
                ->title('Hotel added to favorite successfully')
                ->duration(7000)
                ->success()
                ->send();
        } else {
            return Notification::make()
                ->title('Hotel is already in your favorites')
                ->duration(7000)
                ->info()
                ->send();
        }
    }

    // Optimized filter reset with cache clearing
    public function resetFilters()
    {
        $this->name = '';
        $this->low = null;
        $this->high = null;
        $this->filterAmenities = [];
        $this->sortBy = '';
        $this->resetPage();

        // Clear any related caches if needed
        if ($cityId = $this->city->id ?? session('city')) {
            Cache::forget("city_amenities_{$cityId}");
        }

        // Dispatch an Alpine.js event to clear input fields
        $this->dispatch('reset-filters');
    }

    // Optimized event handlers
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
        $this->resetPage();
    }

    public function updatedLow()
    {
        $this->resetPage();
    }

    public function updatedHigh()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Initialize city efficiently
        if (!isset($this->city) && session()->has('city')) {
            $cityId = session('city');
            $this->city = CityCopy::find($cityId);
        }
    }

    // Optimized sorting method
    public function setSortBy($value)
    {
        $this->sortBy = $value;
        $this->resetPage();
    }

    // Method to clear amenity cache when needed
    public function clearAmenityCache()
    {
        if ($cityId = $this->city->id ?? session('city')) {
            Cache::forget("city_amenities_{$cityId}");
        }
    }
}
