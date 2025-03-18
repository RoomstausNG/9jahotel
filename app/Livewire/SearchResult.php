<?php

namespace App\Livewire;

use App\Models\Hotel;
use App\Models\Amenity;
use Livewire\Component;
use App\Models\CityCopy;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SearchResult extends Component
{
    public CityCopy $city;
    public $name = '';
    public $low = null;
    public $high = null;
    public $filterAmenities = []; // Array to store selected amenity IDs
    public $sortBy = ''; // Property for sorting options
    public $page = 1; // Current page for pagination
    public $perPage = 12; // Items per page

    protected $queryString = [
        'name' => ['except' => ''],
        'low' => ['except' => null],
        'high' => ['except' => null],
        'filterAmenities' => ['except' => []],
        'sortBy' => ['except' => ''],
    ];

    public function getStaticMapUrl($cityName, $zoom = 1, $width = 600, $height = 300)
    {
        // Cache the map URL for 24 hours to avoid repeated API calls
        return Cache::remember("map_url_{$cityName}_{$zoom}_{$width}_{$height}", 60 * 60 * 24, function () use ($cityName, $zoom, $width, $height) {
            try {
                // First, get coordinates using Nominatim with proper headers
                $encodedCity = urlencode(Str::replace('-', '', $cityName));

                $response = Http::withHeaders([
                    'User-Agent' => 'YourAppName/1.0 (your@email.com)',  // Replace with your app name and contact email
                ])->get("https://nominatim.openstreetmap.org/search", [
                    'format' => 'json',
                    'q' => $cityName
                ]);

                $data = $response->json();

                if (empty($data)) {
                    return "https://via.placeholder.com/{$width}x{$height}?text=Map+not+found";
                }

                $lat = $data[0]['lat'];
                $lon = $data[0]['lon'];

                // Use static OSM service
                return "https://staticmap.openstreetmap.de/staticmap.php?center={$lat},{$lon}&zoom={$zoom}&size={$width}x{$height}&maptype=mapnik";
            } catch (\Exception $e) {
                // Log the error
                \Log::error('Map error: ' . $e->getMessage());

                // Return a placeholder
                return "https://via.placeholder.com/{$width}x{$height}?text=Map+unavailable";
            }
        });
    }

    // Reset all filters
    public function resetFilters()
    {
        $this->name = '';
        $this->low = null;
        $this->high = null;
        $this->filterAmenities = [];
        $this->sortBy = '';
        $this->page = 1;
    }

    // Increase page for "Load More" functionality
    public function increasePage()
    {
        $this->page++;
    }

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

        // Apply price range filters if provided
        if ((!is_null($this->low) && is_numeric($this->low)) || (!is_null($this->high) && is_numeric($this->high))) {
            $query->whereExists(function ($subQuery) {
                $subQuery->select(\DB::raw(1))
                    ->from('types')
                    ->whereRaw('types.hotel_id = hotels.id');

                if (!is_null($this->low) && is_numeric($this->low)) {
                    $subQuery->havingRaw('MIN(types.price1) >= ?', [$this->low]);
                }

                if (!is_null($this->high) && is_numeric($this->high)) {
                    $subQuery->havingRaw('MIN(types.price1) <= ?', [$this->high]);
                }
            });
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
            'class_high' => ['column' => 'property_class', 'direction' => 'desc'],
            'class_low' => ['column' => 'property_class', 'direction' => 'asc'],
        ];

        // Apply sorting based on selected option
        if (isset($sortOptions[$this->sortBy])) {
            $sort = $sortOptions[$this->sortBy];
            $query->orderBy($sort['column'], $sort['direction']);
        } elseif ($this->sortBy === 'price_low' || $this->sortBy === 'price_high') {
            // Handle price sorting using the types relationship
            $direction = $this->sortBy === 'price_low' ? 'asc' : 'desc';

            // Use a subquery to get the minimum price for each hotel
            $query->select('hotels.*')
                ->leftJoin('types', 'hotels.id', '=', 'types.hotel_id')
                ->groupBy('hotels.id')
                ->orderBy(\DB::raw('MIN(types.price1)'), $direction);
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

        // Get hotels with pagination
        $limit = $this->perPage * $this->page;
        $hotels = $query->limit($limit)->get();

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
        $mapUrl = $this->getStaticMapUrl($this->city->name);

        return view('livewire.search-result', [
            'hotels' => $hotels,
            'amenities' => $amenities,
            'mapUrl' => $mapUrl,
            'totalFilteredCount' => $totalFilteredCount,
        ]);
    }
}