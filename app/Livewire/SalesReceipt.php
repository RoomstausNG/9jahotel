<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class SalesReceipt extends Component
{
    public Order $sale;


    public function render()
    {
        $sales = $this->sales;
        return view('livewire.sales-receipt');
    }
}
