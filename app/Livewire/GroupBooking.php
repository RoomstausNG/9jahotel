<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

class GroupBooking extends Component
{

    public $steps = [];


    public function mount()
    {
        $this->steps = [
            'step1' => [
                'title' => 'Step 1',
                'description' => 'Select the number of rooms you want to book',
                'component' => 'group-booking.step1',
                'completed' => false,
            ],
            'step2' => [
                'title' => 'Step 2',
                'description' => 'Enter the details of the guests',
                'component' => 'group-booking.step2',
                'completed' => false,
            ],
            'step3' => [
                'title' => 'Step 3',
                'description' => 'Enter the details of the person making the booking',
                'component' => 'group-booking.step3',
                'completed' => false,
            ],
            'step4' => [
                'title' => 'Step 4',
                'description' => 'Review and confirm your booking',
                'component' => 'group-booking.step4',
                'completed' => false,
            ],
        ];
    }

    #[Layout("components.layouts.frontend")]
    public function render()
    {
        return view('livewire.group-booking');
    }
}