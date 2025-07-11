<?php

namespace App\Livewire;

use App\Models\Type;
use App\Models\Hotel;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;

class HotelDetails extends Component
{
    public Hotel $hotel;
    public $category;
    public $roomCategories = [];

    public $review = false;
    public $modal = false;

    public function mount(Hotel $hotel)
    {
        $this->hotel = $hotel->load([
            'roomCategory.rooms' => function ($query) {
                // Only load necessary fields to reduce data transfer
                $query->select('id', 'type_id', 'image', 'number', 'status');
            },
            'amenities' => function ($query) {
                $query->select('amenities.id', 'name', 'icon');
            },
            'faqs' => function ($query) {
                // Using the correct column names from the faqs table
                $query->select('id', 'hotel_id', 'question_id', 'answer');
            },
            'faqs.question' => function ($query) {
                $query->select('id', 'text', 'icon');
            },
            'city' => function ($query) {
                $query->select('id', 'name');
            },
            'ratings' => function ($query) {
                $query->select('id', 'hotel_id', 'number');
            }
        ]);

        // Pre-process room categories to avoid N+1 queries in the view
        $this->prepareRoomCategories();

        // $this->hotel->load(['amenities', 'types']);
    }

    protected function prepareRoomCategories()
    {
        $checkin = Session::get('checkin');
        $checkout = Session::get('checkout');

        $this->roomCategories = $this->hotel->roomCategory->map(function ($category) use ($checkin, $checkout) {
            // Find the first room with an image
            $firstImage = $category->rooms->whereNotNull('image')->first()?->image;

            // Count available rooms
            $available = $category->rooms
                ->filter(fn($room) => $room->isAvailable($checkin, $checkout))
                ->count();

            // Calculate the discounted price
            $discountedPrice = calculateRemainingAmount($category->price1, $this->hotel->online_price);

            return [
                'id' => $category->id,
                'name' => $category->name,
                'price1' => $category->price1,
                'firstImage' => $firstImage,
                'available' => $available,
                'discountedPrice' => $discountedPrice,
                'model' => $category // Keep the original model for compatibility
            ];
        });
    }

    public function selectRoom(Type $category)
    {
        $this->category = $category->load(['rooms' => function ($query) {
            $checkin = Session::get('checkin');
            $checkout = Session::get('checkout');

            $query->select('id', 'type_id', 'number', 'status', 'image');
        }]);

        $this->modal = true;
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        // Generate SEO metadata for this hotel
        $seoData = [
            'hotelName' => $this->hotel->name,
            'description' => "Book your stay at {$this->hotel->name} in {$this->hotel->city->name}. " .
                substr($this->hotel->description ?? '', 0, 150) .
                (strlen($this->hotel->description ?? '') > 150 ? '...' : ''),
            'keywords' => "hotels in {$this->hotel->city->name}, {$this->hotel->name}, hotel booking, Nigeria accommodation, 9jahotels",
            'image' => $this->hotel->background_image ? asset('storage/' . $this->hotel->background_image) : asset('img/9jahotel_logo.png'),
            'type' => 'article'
        ];

        return view('livewire.hotel-details', [
            'roomCategories' => $this->roomCategories,
            'seoData' => $seoData
        ])
            ->with($seoData);
    }
}
