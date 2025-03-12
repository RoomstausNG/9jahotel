<?php

namespace App\Livewire;


use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\Eloquent\Builder;
use Unicodeveloper\Paystack\Facades\Paystack;

class InvoiceList extends Component
{
    public $quantity, $item_id, $booking_id, $sale_id, $discount = 0, $customer_id, $total_price, $agent_id, $mode_of_payment, $type;
    use WithPagination;

    public $sale;
    public $perPage = 10;
    public $query = '';

    public $saleForm = false;
    public $orderForm = false;

    public $updateC = false;

    public function search()
    {
        $this->resetPage();
    }

    public $cid;
    public $confirm = false;
    public $update = false;
    public $form = false;

    public ?array $checked = [];
    public $sortField = 'id';
    public $sortAsc = true;

    function showForm()
    {
        $this->form = true;
    }



    public function sortBy($field)
    {

        if ($this->sortField == $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortField = $field;
    }

    function updatingSearch()
    {
        $this->resetPage();
    }

    function updatedSearch()
    {
        $this->resetPage();
    }

    function updatedPerPage()
    {
        $this->resetPage();
    }

    public function payInvoice(Invoice $invoice)
    {
        // dd($invoice);
        // return  $this->dispatch('pk', invoice: $invoice);


        $data = array(
            'first_name' => auth()->user()->firstname,
            'last_name' => auth()->user()->lastname,
            "amount" => $invoice->amount * 100,
            "reference" => strtoupper($invoice->reference . Str::random(2)),
            "email" => myHotel()->email,
            "currency" => "NGN",
            "orderID" => $invoice->id,
            'description' => 'Subscription for Payment',
            'metadata' => [
                'hotel_name' => myHotel()->name,
                'reference' => $invoice->reference,
                'expired_on' => $invoice->to
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
    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();

        dd($paymentDetails);
        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
    }

    #[On('savePayment')]
    public function updateInvoice($invoice)
    {
        // dd($invoice['id']);
        $inv = Invoice::find($invoice['id']);

        $done = $inv->update(['status' => 'paid', 'date_paid' => now()]);

        // update hotel renew date
        $inv->hotel()->update(['renew' => $inv->to, 'status' => 'active']);

        if ($done) {
            $details = [
                'text' => 'Roomstatus has received your payment, and your subscription is successful',
                'title' => 'Payment successful',
                'timer' => 5000
            ];

            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }


    public function render()
    {
        $term = "%" . $this->query . "%";
        $invoices = Invoice::with(['hotel'])
            ->where('reference', 'LIKE', trim($term))
            ->paginate($this->perPage);
        return view('livewire.invoice-list', compact(['invoices']));
    }
}
