<?php

namespace App\Livewire;

use App\Models\Hotel;
use App\Models\Amenity;
use Livewire\Component;
use App\Models\CityCopy;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Traits\PerformanceMonitoring;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class HallResult extends Component
{
    use WithPagination, PerformanceMonitoring;

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

    /**
     * Get optimized halls query with single database call
     */
    private function getOptimizedHallsQuery(int $cityId, int $capacity): array
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
            ->whereHas('halls') // Only hotels with halls
            ->where('hall_capacity', '>=', $capacity)
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
     * Apply price range filters for halls
     */
    private function applyPriceFilters($query): void
    {
        if (!empty($this->low) && is_numeric($this->low)) {
            $query->where('hotels.hall_lowest_price', '>=', floatval($this->low));
        }
        
        if (!empty($this->high) && is_numeric($this->high)) {
            $query->where('hotels.hall_lowest_price', '<=', floatval($this->high));
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
     * Apply sorting with optimized queries for halls
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
                $query->orderBy('hotels.hall_lowest_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('hotels.hall_lowest_price', 'desc');
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
     * Get city amenities with optimized query
     */
    private function getCityAmenities(int $cityId)
    {
        return Amenity::select('amenities.id', 'amenities.name')
            ->join('amenity_hotel', 'amenities.id', '=', 'amenity_hotel.amenity_id')
            ->join('hotels', 'amenity_hotel.hotel_id', '=', 'hotels.id')
            ->where('hotels.city_id', $cityId)
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('halls')
                    ->whereColumn('halls.hotel_id', 'hotels.id');
            })
            ->distinct()
            ->orderBy('amenities.name')
            ->get();
    }

    // Optimized event handlers
    public function updatedLow()
    {
        $this->resetPage();
    }

    public function updatedHigh()
    {
        $this->resetPage();
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        return $this->withPerformanceMonitoring(function() {
            // Get city ID from the city model or session
            $cityId = $this->city->id ?? session('city');
            $capacity = (int) session('capacity') ?? 50;

            if (!$cityId) {
                // Handle case where no city is selected
                return view('livewire.hall-result', [
                    'hotels' => collect(),
                    'amenities' => collect(),
                    'totalFilteredCount' => 0,
                ]);
            }

            // Get optimized results with single query
            $results = $this->getOptimizedHallsQuery($cityId, $capacity);
            $hotels = $results['hotels'];
            $totalFilteredCount = $results['total'];

            // Get amenities with optimized query
            $amenities = $this->getCityAmenities($cityId);

            // SEO metadata for the hall results page
            $cityName = $this->city->name ?? CityCopy::find($cityId)->name ?? 'Nigeria';
            $this->title = "Event Halls in {$cityName} | 9jahotel";
            $this->description = "Find and book event halls, conference rooms and meeting spaces in {$cityName}. Perfect for events, meetings, and conferences.";
            $this->keywords = "event halls in {$cityName}, conference rooms, meeting spaces, venue booking, Nigeria, event spaces, 9jahotel";

            return view('livewire.hall-result', [
                'hotels' => $hotels,
                'amenities' => $amenities,
                'totalFilteredCount' => $totalFilteredCount,
            ]);
        }, 'hall-search-render');
    }
}
