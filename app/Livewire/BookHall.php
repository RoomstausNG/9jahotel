<?php

namespace App\Livewire;

use App\Models\Hall;
use App\Models\Booking;
use App\Models\Invoice;
use Livewire\Component;
use App\Models\Customer;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use Silber\PageCache\Cache;
use App\Services\OpayService;
use App\Models\BookingInvoice;
use Filament\Forms\Components;
use Livewire\Attributes\Layout;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Unicodeveloper\Paystack\Facades\Paystack;
use Filament\Forms\Concerns\InteractsWithForms;

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

            $this->dispatch('swal:success', details: sweetAlert(...$details));

            sleep(5);

            return redirect("/");
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
        // Generate referral code: 8 uppercase chars, exclude 0,O,S,5
        $referral_code = $this->generateReferralCode();
        return Customer::firstOrCreate(
            ['email' => $data['email']],
            [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'phone' => $data['phone'],
                'referral_code' => $referral_code,
            ]
        );
    }

    private function generateReferralCode(): string
    {
        $chars = 'ABCDEFGHIJKLMNPQRTUVWXYZ12346789'; // Exclude 0,O,S,5
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
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

        $reference = $booking->reference;

        $paymentData = [
            'reference' => strtoupper($booking->reference),
            'amount' => $booking->amount, // Remove * 100 as OPay expects actual amount
            'currency' => 'NGN',
            'callback_url' => route('opay.callback'),
            'return_url' => route('opay.return', $reference), // Use separate return URL for user redirects
            'country' => 'NG',
            'customer_name' => $booking->customer->firstname . ' ' . $booking->customer->lastname,
            'customer_email' => $booking->customer->email,
            'customer_phone' => $booking->customer->phone ?? '', // Add phone if available
            'product_name' => 'Hall Booking - ' . ucfirst($booking->hotel->name),
            'product_description' => 'Hall booking payment for ' . ucfirst($booking->hotel->name) . ' (Ref: ' . $booking->reference . ')',
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

    public function mount()
    {
        $this->hotel = $this->hall->hotel;
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        return view('livewire.book-hall');
    }
}