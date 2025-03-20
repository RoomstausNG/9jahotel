<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

class GroupBooking extends Component
{
    #[Layout("components.layouts.frontend")]
    public function render()
    {
        return view('livewire.group-booking');
    }
}
