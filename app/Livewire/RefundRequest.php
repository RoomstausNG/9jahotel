<?php

namespace App\Livewire;

use App\Models\BookingInvoice;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class RefundRequest extends Component
{
    public BookingInvoice $invoice;
    public $bank_name;
    public $account_no;
    public $account_name;
    public $reason;
    public $other_reason;

    protected $rules = [
        'bank_name' => 'required|string|max:100',
        'account_no' => 'required|numeric|digits:10',
        'account_name' => 'required|string|max:100',
        'reason' => 'required|string',
        'other_reason' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        // $this->invoice = BookingInvoice::where('reference', $reference)->with('bookings', 'customer')->firstOrFail();
        $this->account_name = $this->invoice->customer->name;
        $this->invoice->load([
            'bookings.bookable',
            'customer',
            'hotel'
        ]);
    }

    public function submit()
    {
        $this->validate();
        $reasonText = $this->reason === 'other' ? $this->other_reason : $this->reason;
        $refund = $this->invoice->refund()->create([
            'bank_name' => $this->bank_name,
            'account_no' => $this->account_no,
            'account_name' => $this->account_name,
            'reason' => $this->reason,
            'other_reason' => $this->other_reason,
            'status' => 'pending',
        ]);
        $data = [
            'invoice' => $this->invoice,
            'refund' => $refund,
            'bank_name' => $this->bank_name,
            'account_no' => $this->account_no,
            'account_name' => $this->account_name,
            'reason' => $reasonText,
        ];
        Mail::to('refund@9jahotel.com')->queue(new \App\Mail\RefundRequestMail($data));
        session()->flash('success', 'Your refund request has been submitted.');

        Notification::make()
            ->title('Refund Request Submitted')
            ->body('Your refund request has been successfully submitted.')
            ->success()
            ->send();
        return redirect('/');
    }

    public array $cancelReasons = [
        'Change of plans',
        'Found a better price',
        'Bad experience at hotel',
        'Personal reasons',
        'Medical emergency',
        'Travel restrictions',
        'Booking mistake',
        'Delayed/cancelled trip',
        'Other',
    ];

    /**
     * List of all Nigeria banks for the select dropdown.
     * @var array
     */
    public array $nigeriaBanks = [
        'Access Bank',
        'Citibank',
        'Ecobank',
        'Fidelity Bank',
        'First Bank',
        'First City Monument Bank',
        'Globus Bank',
        'Guaranty Trust Bank',
        'Heritage Bank',
        'Jaiz Bank',
        'Keystone Bank',
        'Kuda Bank',
        'Parallex Bank',
        'Polaris Bank',
        'PremiumTrust Bank',
        'Providus Bank',
        'Stanbic IBTC Bank',
        'Standard Chartered Bank',
        'Sterling Bank',
        'SunTrust Bank',
        'Titan Trust Bank',
        'Union Bank',
        'United Bank for Africa',
        'Unity Bank',
        'Wema Bank',
        'Zenith Bank',
    ];

    #[Layout('components.layouts.frontend')]
    public function render()
    {

        return view('livewire.refund-request', [
            'cancelReasons' => $this->cancelReasons,
            'nigeriaBanks' => $this->nigeriaBanks,
        ]);
    }
}
