<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Invoice;
use Livewire\Component;
use App\Models\Customer;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use App\Models\BookingInvoice;
use Filament\Forms\Components;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Unicodeveloper\Paystack\Facades\Paystack;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Attributes\Layout;
use App\Models\Hall;

class BookHall extends Component implements HasForms
{
    use InteractsWithForms;
    public Hall $hall;

    public $hotel;

    public $due;

    public $firstname, $lastname, $email, $phone;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('firstname')
                    ->label('First Name')
                    ->required(),
                Components\TextInput::make('lastname')
                    ->label('Last Name')
                    ->required(),
                Components\TextInput::make('email')
                    // ->unique(table: Customer::class)
                    ->label('Email address')
                    ->required()
                    ->validationMessages([
                        'unique' => 'The :attribute has already been registered, kindly login to proceed',
                    ]),
                Components\TextInput::make('phone')
                    ->unique()
                    ->label('Phone Number')
                    ->required(),
            ])->columns(2);
    }

    public function addBooking($type)
    {
        if (!$this->hall) {
            $details = [
                'icon' => 'error',
                'text' => 'Kindly select from the available halls from the list',
                'title' => 'No Hall selected'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        } else {
            $hall = $this->hall;
            $customer = $this->getOrCreateCustomer($this->form->getState());

            $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));

            $data['checked_in'] = Session::get('checkin');
            $data['checked_out'] = Session::get('checkout');
            if ($type == 'non-refundable') {
                $data['discount'] = $this->hall->hotel->non_refundable_price;
                $data['due'] = calculateRemainingAmount($this->hall->price * $days, $this->hall->hotel->non_refundable_price);
                $this->due = calculateRemainingAmount($this->hall->price * $days, $this->hall->hotel->non_refundable_price);
            } else {
                $data['discount'] = $this->hall->hotel->online_price;
                $data['due'] = calculateRemainingAmount($this->hall->price * $days, $this->hall->hotel->online_price);
                $this->due = calculateRemainingAmount($this->hall->price * $days, $this->hall->hotel->online_price);
            }

            // booking invoice
            $invoice = BookingInvoice::create([
                'customer_id' => $customer->id,
                'hotel_id' => $this->hall->hotel_id,
                'amount' => $this->due,
                'status' => 'unpaid',
                'checked_in' => $data['checked_in'], // ensure checkin is saved
                'checked_out' => $data['checked_out'], // ensure checkout is saved
            ]);

            $data['booking_invoice_id'] = $invoice->id;

            // create bookings

            $data['bookable_id'] = $hall->id;
            $created = $this->createBooking($hall, $data, $customer);


            // associate customer if not exist before
            $this->associateCustomer($customer->id);
        }

        if ($type == 'payAtTheHotel') {
            $details = [
                'text' => 'Please check your email for confirmation\nKindly note that UNPAID Bookings are subject to\ncancellation with or without notice by the Hotel.',
                'title' => 'Your Booking is Received!'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        } else {
            return $this->payforBooking($invoice);
        }
    }

    public function calculateDue($booking, $hall)
    {
        $normal = $hall->price * calculateDateDifferenceInDays($booking->checked_in, $booking->checked_out);
        $discounted = $normal - ($normal * $booking->discount / 100);
        $booking->update([
            'due' => $booking->discount > 0 ? $discounted : $normal
        ]);
        return $booking;
    }

    public function getOrCreateCustomer($data): Customer
    {
        return Customer::firstOrCreate(
            ['email' => $data['email']],
            [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'phone' => $data['phone'],
            ]
        );
    }

    public function createBooking($hall, $data, $customer): Booking
    {
        return Booking::create([
            'bookable_id' => $hall->id,
            'bookable_type' => 'App\\Models\\Hall',
            'type' => 'hall',
            'booking_invoice_id' => $data['booking_invoice_id'],
            'discount' => $data['discount'],
            'checked_in' => $data['checked_in'],
            'checked_out' => $data['checked_out'],
            'status' => 'unpaid',
            'agent_id' => null,
            'via' => '9jahotel',
            'due' => $data['due'],
            'hotel_id' => $hall->hotel_id,
            'customer_id' => $customer->id
        ]);
    }

    public function payforBooking(BookingInvoice $booking)
    {

        $data = array(
            'first_name' => $booking->customer->firstname,
            'last_name' => $booking->customer->lastname,
            "amount" => $booking->amount * 100,
            "reference" => strtoupper($booking->reference),
            "email" => $booking->customer->email,
            "currency" => "NGN",
            "orderID" => $booking->id,
            'description' => 'Booking payment for ' . ucfirst($booking->hotel->name),
            'metadata' => [
                'hotel_name' => ucfirst($booking->hotel->name),
                'reference' => $booking->reference
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
    public function updateInvoice($cid)
    {
        // dd($invoice['id']);
        $booking = Booking::find($cid);

        $done = $booking->update(['status' => 'paid', 'date_paid' => now()]);

        if ($done) {
            $details = [
                'text' => '9jahotel has received your payment, and your booking is successful',
                'title' => 'Payment successful',
                'timer' => 5000
            ];

            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function associateCustomer($id)
    {
        $customer = Customer::find($id);

        if (!$customer->hotels()->find($this->hall->hotel_id)) {
            $customer->hotels()->attach($this->hall->hotel_id);
        }
    }

    public  function mount()
    {
        $this->hotel = $this->hall->hotel;
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        return view('livewire.book-hall');
    }
}
