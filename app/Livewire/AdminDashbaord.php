<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;

class AdminDashbaord extends Component
{
    public $currentMonth, $numberOfDays;


    function nextMonth()
    {
    }

    function prevMonth()
    {
    }

    function mount()
    {
        $this->currentMonth = Carbon::now()->month;
        $this->numberOfDays = Carbon::now()->daysInMonth;
    }

    public function render()
    {
        return view('livewire.admin-dashbaord');
    }
}
