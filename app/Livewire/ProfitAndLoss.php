<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Revenue;
use Livewire\Component;
use App\Models\Expenditure;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class ProfitAndLoss extends Component
{
    public ?object $expenditures;
    public ?object $incomes;
    public $orders = 0, $hall = 0, $room = 0,  $start, $end, $showTable = false;

    function checkHistories()
    {
        $data = $this->validate(
            [
                'start' => ['required', 'date', 'before_or_equal:today'],
                'end' => ['required', 'date', 'after_or_equal:start']
            ],
            [
                'start.required' => 'The start date cannot be empty',
                'start.before_or_equal' => 'The start date must be a past date',
                'end.required' => 'The end date cannot be empty',
                'end.before_or_equal' => 'The start date cannot be a future date',
            ]
        );

        // expenditures
        $this->expenditures = Expenditure::has('finances')
            ->withSum('finances as total', 'amount')
            ->whereHas(
                'finances',
                fn (Builder $query) =>
                $query->whereDate('created_at', '>=', $this->start)
                    ->whereDate('created_at', '<=', $this->end)
                // fn (Builder $query) => $query->whereBetween('created_at', [$this->start, $this->end])
            )
            ->get();

        // incomes
        $this->incomes = Revenue::has('finances')
            ->withSum('finances as total', 'amount')
            ->whereHas(
                'finances',
                fn (Builder $query) =>
                $query->whereDate('created_at', '>=', $this->start)
                    ->whereDate('created_at', '<=', $this->end)
                // fn (Builder $query) => $query->whereBetween('created_at', [$this->start, $this->end])
            )
            ->get();

        // hall reservation total income
        $this->hall = Booking::amountGenerated('App\Models\Hall', $this->start, $this->end);


        // room reservation total income
        $this->room = Booking::amountGenerated('App\Models\Room', $this->start, $this->end);

        // sales order
        $this->orders = Order::sum('total_price');


        $this->showTable = true;
    }

    public function render()
    {
        return view('livewire.profit-and-loss');
    }
}
