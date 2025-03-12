<?php

namespace App\Livewire;

use App\Models\Booking;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Builder;

class GuestReport extends Component
{
    public $date, $bookings;



    public function mount($date)
    {
        $this->bookings = Booking::where('bookable_type', 'App\Models\Room')
            ->whereHas('statuses', fn (Builder $query) => $query->where('date', $date))
            ->where('status', 'occupied')->get();
    }

    #[Layout('layouts.blank')]
    public function render()
    {
        return view('livewire.guest-report');
    }
}
