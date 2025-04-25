<?php

namespace App\Livewire;

use App\Models\Type;
use App\Models\Hotel;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;

class HotelHallDetails extends Component
{
    public Hotel $hotel;
    public $halls;

    public $review = false;
    public $modal = false;

    public function mount(Hotel $hotel)
    {
        $this->hotel = $hotel->load([
            'halls',
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
        $this->prepareHalls();

        // $this->hotel->load(['amenities', 'types']);
    }

    protected function prepareHalls()
    {
        $checkin = Session::get('checkin');
        $checkout = Session::get('checkout');
        $capacity = Session::get('capacity');

        $this->halls = $this->hotel->halls->map(function ($category) use ($checkin, $checkout) {

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
                'available' => $available,
                'discountedPrice' => $discountedPrice,
                'model' => $category // Keep the original model for compatibility
            ];
        });
    }

    public function selectHall()
    {
        $this->category = $category->load([
            'rooms' => function ($query) {
                $checkin = Session::get('checkin');
                $checkout = Session::get('checkout');

                $query->select('id', 'type_id', 'number', 'status', 'image');
            }
        ]);

        $this->modal = true;
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        return view('livewire.hall-details', [
            'roomCategories' => $this->roomCategories
        ]);
    }
}