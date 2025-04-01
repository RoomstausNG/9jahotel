<?php

namespace App\Livewire;

use App\Models\Hotel;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Unicodeveloper\Paystack\Facades\Paystack;

class RenewalPage extends Component
{
    public Hotel $hotel;

    public $amount, $invoice;


    public function pay()
    {
        $data = $this->validate(['amount' => ['required', 'numeric']]);
        $data = array(
            'first_name' => auth()->user()->firstname,
            'last_name' => auth()->user()->lastname,
            "amount" => $this->amount * 100,
            "reference" => strtoupper($this->invoice->reference . Str::random(2)),
            "email" => $this->hotel->email,
            "currency" => "NGN",
            "orderID" => $this->invoice->id,
            'description' => 'Subscription for Payment',
            'metadata' => [
                'hotel_name' => $this->hotel->name,
                'reference' => $this->invoice->reference,
                'expired_on' => $this->invoice->to
            ]
        );


        try {
            return  Paystack::getAuthorizationUrl($data)->redirectNow();
        } catch (\Exception $e) {
            $details = [
                'icon' => 'error',
                'text' => $e->getMessage(),
                'title' => 'Payment Failed'
            ];

            return $this->dispatch('swal:success', details: sweetAlert(...$details));
            // return Redirect::back()->withMessage(['msg' => 'The paystack token has expired. Please refresh the page and try again.', 'type' => 'error']);
        }
    }
    public function mount()
    {

        if ($this->hotel->status == 'active') {
            return redirect()->route('dashboard');
        }

        $this->invoice = $this->hotel->invoices()->where('status', 'unpaid')->latest()->first();
        $this->amount = $this->invoice->amount;
    }

    #[Layout('layouts.auth')]
    public function render()
    {
        return view('livewire.renewal-page');
    }
}
