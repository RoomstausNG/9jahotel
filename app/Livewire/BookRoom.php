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
use App\Services\OpayService;
use App\Models\BookingInvoice;
use Filament\Forms\Components;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Redirect;
use Unicodeveloper\Paystack\Facades\Paystack;
use Filament\Forms\Concerns\InteractsWithForms;

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
                'status' => 'unpaid',
                'checked_in' => $data['checked_in'], // ensure checkin is saved
                'checked_out' => $data['checked_out'], // ensure checkout is saved
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
                'text' => 'Please check your email for confirmation
Kindly note that UNPAID Bookings are subject to
cancellation with or without notice by the Hotel.',
                'title' => 'Your Booking is Received!'
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
        $reference = $booking->reference;

        $paymentData = [
            'reference' => strtoupper($reference),
            'amount' => $booking->amount,
            'currency' => 'NGN',
            'callback_url' => route('opay.callback'),
            'return_url' => route('opay.return'),
            'country' => 'NG',
            'customer_name' => $booking->customer->firstname . ' ' . $booking->customer->lastname,
            'customer_email' => $booking->customer->email,
            'customer_phone' => $booking->customer->phone ?? '',
            'product_name' => 'Hotel Booking - ' . ucfirst($booking->hotel->name),
            'product_description' => 'Booking payment for ' . ucfirst($booking->hotel->name) . ' (Ref: ' . $reference . ')',
            // Optional: Add payment method if you want to specify one
            // 'pay_method' => 'BankCard', // or other supported methods
        ];

        try {
            $opayService = new OpayService();
            $response = $opayService->createExpressCheckout($paymentData);

            if ($response['status'] === 'success' && isset($response['cashier_url'])) {
                Cache::put($reference, $booking->reference, now()->addMinutes(30));
                return redirect()->away($response['cashier_url']);
            } else {
                throw new \Exception('Failed to get OPay cashier URL.');
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('OPay payment initiation failed', [
                'booking_id' => $booking->id,
                'reference' => $booking->reference,
                'error' => $e->getMessage()
            ]);

            $details = [
                'icon' => 'error',
                'text' => $e->getMessage(),
                'title' => 'Payment Failed'
            ];

            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }
    /**
     * Obtain OPay payment information
     * This method is called via HTTP route, so it must return a proper HTTP response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handleGatewayCallback()
    {
        $opayService = new OpayService();
        $reference = request()->query('reference');

        if ($reference) {
            try {
                $paymentDetails = $opayService->verifyPayment($reference);

                \Log::info('Payment verification initiated', [
                    'reference' => $reference,
                    'payment_details' => $paymentDetails
                ]);

                if ($paymentDetails['status'] === 'success' && $paymentDetails['payment_status'] === 'SUCCESS') {
                    // Payment was successful, update your invoice
                    $bookingInvoice = BookingInvoice::where('reference', $reference)->first();
                    if ($bookingInvoice) {
                        // Update the invoice and associated bookings
                        $bookingInvoice->update(['status' => 'paid', 'date_paid' => now()]);

                        // Update all associated bookings
                        Booking::where('booking_invoice_id', $bookingInvoice->id)
                            ->update(['status' => 'paid', 'date_paid' => now()]);

                        \Log::info('Payment successful', [
                            'reference' => $reference,
                            'booking_invoice_id' => $bookingInvoice->id
                        ]);
                    }

                    // Redirect to a success page with flash message
                    return redirect()->route('booking.success')
                        ->with('success', 'Payment successful for booking ' . $reference);
                } else {
                    \Log::error('Payment verification failed', [
                        'reference' => $reference,
                        'payment_details' => $paymentDetails ?? "nothing returned"
                    ]);

                    // Redirect to a failure page with flash message
                    return redirect()->route('booking.failed')
                        ->with('error', 'Payment verification failed for booking ' . $reference);
                }
            } catch (\Exception $e) {
                \Log::error('Payment verification error', [
                    'reference' => $reference,
                    'error' => $e->getMessage()
                ]);

                // Redirect to a failure page with flash message
                return redirect()->route('booking.failed')
                    ->with('error', 'Error verifying payment: ' . $e->getMessage());
            }
        } else {
            \Log::error('Payment callback without reference');

            // Redirect to a failure page with flash message
            return redirect()->route('booking.failed')
                ->with('error', 'Payment reference not found in callback.');
        }
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
