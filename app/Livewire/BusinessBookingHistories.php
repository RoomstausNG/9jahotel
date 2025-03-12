<?php

namespace App\Livewire;

use App\Models\Booking;
use Livewire\Component;
use App\Models\Business;
use Livewire\WithPagination;

class BusinessBookingHistories extends Component
{
    use WithPagination;

    public Business $business;
    public function render()
    {
        $customers = $this->business->customers()->select('id')->pluck('id')->toArray();
        $bookings = Booking::whereIn('customer_id', $customers)->orderByDesc("id")->paginate(10);
        return view('livewire.business-booking-histories', compact(['bookings']));
    }
}
