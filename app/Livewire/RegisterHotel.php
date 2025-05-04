<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

class RegisterHotel extends Component
{
    #[Layout('layouts.frontend')]
    public function render()
    {
        return view('livewire.register-hotel');
    }
}
