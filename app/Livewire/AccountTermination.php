<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Bus;
use Livewire\Attributes\Layout;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Jobs\DeleteCustomerJob;

class AccountTermination extends Component
{
    public $email;
    public $showConfirmation = false;
    public $message;

    public function terminateAccount()
    {
        $validated = Validator::make(['email' => $this->email], [
            'email' => 'required|email|exists:customers,email',
        ])->validate();

        $customer = Customer::where('email', $this->email)->first();
        if ($customer) {
            // Dispatch job to queue for background deletion
            DeleteCustomerJob::dispatch($customer->id);
            $this->showConfirmation = true;
            $this->message = 'Your account deletion request has been received and will be processed shortly.';
            $this->dispatchBrowserEvent('swal:success', [
                'text' => $this->message,
                'title' => 'Account Deletion Requested',
            ]);
        } else {
            $this->showConfirmation = false;
            $this->message = 'No account found with this email.';
            $this->dispatch('swal:success', [
                'text' => $this->message,
                'title' => 'Notice',
            ]);
        }
        $this->reset('email');
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        return view('livewire.account-termination');
    }
}
