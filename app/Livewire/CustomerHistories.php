<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerHistories extends Component
{
    use WithPagination;
    public Customer $customer;

    public function render()
    {
        $bookings = $this->customer->bookings()->orderBy("created_at", "desc")->paginate(10);
        return view('livewire.customer-histories', compact(['bookings']));
    }
}
