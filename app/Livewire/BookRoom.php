<?php

namespace App\Livewire;

use App\Models\Room;
use App\Models\Type;
use App\Models\Booking;
use App\Models\Invoice;
use Livewire\Component;
use App\Models\Customer;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Models\BookingInvoice;
use Filament\Forms\Components;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Redirect;
use Unicodeveloper\Paystack\Facades\Paystack;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Attributes\Layout;

class BookRoom extends Component implements HasForms
{
    use InteractsWithForms;
    public Type $category;

    public $hotel;

    public $due;

    public $firstname, $lastname, $email, $phone;
    public ?array $checked = [];

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
        // ->statePath('data');
    }


    public function addBooking($type)
    {
        if (count($this->checked) < 1) {
            $details = [
                'icon' => 'error',
                'text' => 'Kindly select from the available rooms from the list',
                'title' => 'No Room selected'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        } else {
            $rooms = Room::whereIn('id', $this->checked)->get();
            $customer = $this->getOrCreateCustomer($this->form->getState());

            $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));

            $data['checked_in'] = Session::get('checkin');
            $data['checked_out'] = Session::get('checkout');
            if ($type == 'non-refundable') {
                $data['discount'] = $this->category->hotel->non_refundable_price;
                $data['due'] = calculateRemainingAmount($this->category->price1 * $days, $this->category->hotel->non_refundable_price);
                $this->due = calculateRemainingAmount($this->category->price1 * $days * count($this->checked), $this->category->hotel->non_refundable_price);
            } else {
                $data['discount'] = $this->category->hotel->online_price;
                $data['due'] = calculateRemainingAmount($this->category->price1 * $days, $this->category->hotel->online_price);
                $this->due = calculateRemainingAmount($this->category->price1 * $days * count($this->checked), $this->category->hotel->online_price);
            }

            // booking invoice
            $invoice = BookingInvoice::create([
                'customer_id' => $customer->id,
                'hotel_id' => $this->category->hotel_id,
                'amount' => $this->due,
                'status' => 'unpaid'
            ]);

            $data['booking_invoice_id'] = $invoice->id;

            // create bookings
            foreach ($rooms as $room) {
                $data['bookable_id'] = $room->id;
                $created = $this->createBooking($room, $data, $customer);
            }

            // associate customer if not exist before
            $this->associateCustomer($customer->id);
        }



        // // update the due amount
        // $booking = $this->calculateDue($created, $$this->category);

        if ($type == 'payAtTheHotel') {
            $details = [
                'text' => 'Room booked successfully.',
                'title' => 'Booking Added'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        } else {
            return $this->payforBooking($invoice);
        }
    }

    public function calculateDue($booking, $room)
    {
        $normal = $room->price1 * calculateDateDifferenceInDays($booking->checked_in, $booking->checked_out);
        $discounted = $normal - ($normal * $booking->discount / 100);
        $booking->update([
            // 'hotel_id' => $room->hotel_id,
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

    public function createBooking($room, $data, $customer): Booking
    {
        return Booking::create([
            'bookable_id' => $room->id,
            'bookable_type' => 'App\Models\Room',
            'type' => 'room',
            'booking_invoice_id' => $data['booking_invoice_id'],
            'discount' => $data['discount'],
            'checked_in' => $data['checked_in'],
            'checked_out' => $data['checked_out'],
            'status' => 'unpaid',
            'agent_id' => null,
            'via' => '9jahotel',
            'due' => $data['due'],
            'hotel_id' => $room->hotel_id,
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

        if (!$customer->hotels()->find($this->category->hotel_id)) {
            $customer->hotels()->attach($this->category->hotel_id);
        }
    }

    public  function mount()
    {
        $this->category->load('rooms');
        $this->hotel = $this->category->hotel;
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        return view('livewire.book-room');
    }
}