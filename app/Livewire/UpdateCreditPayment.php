<?php

namespace App\Livewire;


use Livewire\Component;
use App\Models\Business;
use Livewire\WithPagination;
use App\Models\CreditPayment;
use App\Notifications\CreditNotification;
use Illuminate\Support\Facades\Notification;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class UpdateCreditPayment extends Component
{
    use WithPagination, LivewireAlert;
    public $amount_paid;
    public Business $business;


    public function mount()
    {
        $this->amount_paid = $this->business->balance;
    }

    public function pay()
    {
        $data = $this->validate([
            'amount_paid' => ['required', 'numeric', 'min:500'],
        ], ['amount_paid.required' => 'amount must not be empty']);
        // dd($this->getBalance());
        $payed = $this->business->payments()->create([
            'amount_paid' => $data['amount_paid'],
            'amount_owed' => $this->business->balance
        ]);

        if ($payed) {
            $this->business->update([
                'balance' => $this->getBalance()
            ]);
            $this->reset('amount_paid');
            $this->alert('success', 'Payment made successfully!');
        }
    }

    public function getBalance()
    {
        return $this->business->balance - $this->amount_paid;
    }

    public function notifyBusiness(Business $business)
    {
        // send reminder emails
        if ($business->balance < 0) {
            // $done = $business->notify(new CreditNotification($business));
            $done = Notification::send($business, new CreditNotification($business));

            // $details = [
            //     'text' => 'Payment remind sent successfully',
            //     'title' => 'Reminder sent'
            // ];
            // return $this->dispatch('swal:success', details: sweetAlert(...$details));

            $this->alert('success', 'Payment remind sent successfully');
        } else {
            // $this->reset();
            // $details = [
            //     'text' => 'Business has no outstanding payments',
            //     'title' => 'Not Oweing'
            // ];
            // return $this->dispatch('swal:warning', details: sweetAlert(...$details));
            $this->alert('warning', 'Business has no outstanding debt');
        }
        // $this->type = 'corporate';
    }

    public function render()
    {
        $payments = CreditPayment::where('business_id', $this->business->id)->paginate(10);
        return view('livewire.update-credit-payment', compact(['payments']));
    }
}