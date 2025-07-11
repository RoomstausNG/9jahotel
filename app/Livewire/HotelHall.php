<?php

namespace App\Livewire;

use App\Models\Hall;
use App\Models\Type;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;

class HotelHall extends Component
{
    public Hotel $hotel;
    public $hall;
    public $halls = [];

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
        $this->preparehalls();

        // $this->hotel->load(['amenities', 'types']);
    }

    protected function preparehalls()
    {
        $checkin = Session::get('checkin');
        $checkout = Session::get('checkout');
        $capacity = Session::get('capacity');

        $this->halls = $this->hotel->halls()->with('bookings')
            ->where('status', 'available')
            ->where('capacity', '>=', $capacity)
            ->whereDoesntHave('bookings', function (Builder $query) use ($checkin, $checkout) {
                $query->where(function ($q) use ($checkin, $checkout) {
                    $q->where('checked_in', '<', $checkout)
                        ->where('checked_out', '>', $checkin);
                });
            })
            ->get();
    }

    #[Layout('components.layouts.frontend')]
    #[Layout('components.layouts.frontend')]
    public function render()
    {
        // Generate SEO metadata for this hotel's halls
        $seoData = [
            'hotelName' => $this->hotel->name,
            'title' => "Halls at {$this->hotel->name} | 9jahotels",
            'description' => "Book halls and event spaces at {$this->hotel->name} in {$this->hotel->city->name}. Perfect for meetings, conferences, and events.",
            'keywords' => "event halls in {$this->hotel->city->name}, {$this->hotel->name}, conference rooms, meeting spaces, venue booking, Nigeria, 9jahotels",
            'image' => $this->hotel->background_image ? asset('storage/' . $this->hotel->background_image) : asset('img/9jahotel_logo.png'),
            'type' => 'article'
        ];

        return view('livewire.hotel-halls', [
            'halls' => $this->halls
        ])
            ->with($seoData);
    }
}
