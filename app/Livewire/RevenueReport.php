<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Booking;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Models\Business;
use App\Models\Scopes\HotelScope;
use App\Notifications\CreditNotification;
use Illuminate\Support\Facades\DB;

class RevenueReport extends Component
{
    use LivewireAlert;
    public $rooms;
    public $halls;
    public $business;
    public $perPage = 10;
    public $order,  $business_id, $type = 'room', $bookable_id, $bookable_type, $item_id,  $start, $end, $showTable = false;



    function selectType($ty)
    {
        $this->reset();
        $this->type = $ty;
    }

    function checkRooms()
    {
        $data = $this->validate(
            [
                'bookable_id' => 'required',
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

        if ($this->bookable_id != 'all') {
            $this->rooms = Booking::where('bookable_type', 'App\Models\Room')
                ->where('bookable_id', $this->bookable_id)
                ->whereDate('created_at', '>=', $this->start)
                ->whereDate('created_at', '<=', $this->end)->get();
        } else {
            $this->rooms = Booking::where('bookable_type', 'App\Models\Room')
                ->whereDate('created_at', '>=', $this->start)
                ->whereDate('created_at', '<=', $this->end)->get();
        }
        $this->showTable = true;
    }

    function checkHalls()
    {
        $data = $this->validate(
            [
                'bookable_id' => 'required',
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


        if ($this->bookable_id != 'all') {
            $this->halls = Booking::where('bookable_type', 'App\Models\Hall')
                ->where('bookable_id', $this->bookable_id)
                ->whereDate('created_at', '>=', $this->start)
                ->whereDate('created_at', '<=', $this->end)->get();
        } else {
            $this->halls = Booking::where('bookable_type', 'App\Models\Hall')
                ->whereDate('created_at', '>=', $this->start)
                ->whereDate('created_at', '<=', $this->end)->get();
        }
        $this->showTable = true;
    }

    function checkSales()
    {
        $data = $this->validate(
            [
                'item_id' => 'nullable',
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


        if ($this->item_id != 'all') {
            // $this->order = Order::itemSales($this->item_id, 'App\Models\Hall', $this->start, $this->end);
            $this->order = Order::where('item_id', $this->item_id)
                ->whereDate('created_at', '>=', $this->start)
                ->whereDate('created_at', '<=', $this->end)->get();
        } else {
            $this->order = Order::allItemSales($this->start, $this->end);
            $this->order = Order::whereDate('created_at', '>=', $this->start)
                ->whereDate('created_at', '<=', $this->end)->get();
        }

        $this->showTable = true;
    }
    function checkCustomer()
    {
        $data = $this->validate(
            [
                'business_id' => 'nullable',

            ]
        );

        if ($this->business_id == 'all') {
            $this->business =  Business::all();
        } else {
            $this->business =  Business::where('id', $this->business_id)->get();
        }

        $this->showTable = true;
    }

    public function notifyBusiness(Business $business)
    {
        // send reminder emails
        if ($business->balance > 0) {
            $done = $business->notify(new CreditNotification($business));

            // $this->reset();
            // $details = [
            //     'text' => 'Payment remind sent successfully',
            //     'title' => 'Reminder sent'
            // ];
            // return $this->dispatch('swal:success', details: sweetAlert(...$details));

            $this->alert('success', 'Payment reminder sent successfully');
        } else {

            // $details = [
            //     'text' => 'Business has no outstanding payments',
            //     'title' => 'Not Oweing'
            // ];
            // return $this->dispatch('swal:warning', details: sweetAlert(...$details));
            $this->alert('warning', 'Business has no outstanding payments');
        }
        $this->reset();
        $this->type = 'corporate';
    }

    public function render()
    {
        return view('livewire.revenue-report');
    }
}
