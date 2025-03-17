<?php

namespace App\Livewire;

use App\Models\Hotel;
use App\Models\Amenity;
use Livewire\Component;
use App\Models\CityCopy;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

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

    public function getStaticMapUrl($cityName, $zoom = 1, $width = 600, $height = 300)
    {
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
    }

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

        $mapUrl = $this->getStaticMapUrl($this->city->name);

        return view('livewire.search-result', [
            'hotels' => $hotels,
            'amenities' => $amenities,
            'mapUrl' => $this->getStaticMapUrl($this->city->name),
        ]);
    }
}
