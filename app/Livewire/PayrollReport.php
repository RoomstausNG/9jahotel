<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PayrollReport extends Component
{

    #[Layout('layouts.blank')]
    public function render()
    {
        $staff = User::role(['manager', 'frontdesk', 'frontdesk-manager', 'sales-staff', 'sales-manager'])->where('hotel_id', auth()->user()->hotel_id)->get();
        return view('livewire.payroll-report', compact(['staff']));
    }
}