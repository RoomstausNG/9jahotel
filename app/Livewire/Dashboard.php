<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BookingInvoice;

class Dashboard extends Component
{
    public $activeBookings = 0;
    public $canceledBookings = 0;
    public $completedBookings = 0;
    public $favorites = 0;

    public function mount()
    {
        $this->activeBookings = BookingInvoice::whereIn('status', ['active', 'occupied'])->count();
        $this->canceledBookings = BookingInvoice::where('status', 'cancelled')->count();
        $this->completedBookings = BookingInvoice::whereIn('status', ['completed', 'checked_out'])->count();
        $this->favorites = auth()->user()?->favourites()->count() ?? 0;
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'activeBookings' => $this->activeBookings,
            'canceledBookings' => $this->canceledBookings,
            'completedBookings' => $this->completedBookings,
            'favorites' => $this->favorites,
        ]);
    }
}
