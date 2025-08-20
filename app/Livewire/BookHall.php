<?php

namespace App\Livewire;

use App\Models\Hall;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Coupon;
use Livewire\Component;
use App\Models\Customer;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use App\Services\OpayService;
use App\Models\BookingInvoice;
use Filament\Forms\Components;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Contracts\HasForms;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\Session;
use Unicodeveloper\Paystack\Facades\Paystack;
use Filament\Forms\Concerns\InteractsWithForms;

class BookHall extends Component implements HasForms
{
    use InteractsWithForms;
    public Hall $hall;

    public $hotel;

    public $due;

    public $paymentMethods = [];

    public $selectedPaymentMethod = 'opay';

    public $firstname, $lastname, $email, $phone;

    public $checkinDate;
    public $checkoutDate;

    // Coupon properties
    public $couponCode = '';
    public $appliedCoupon = null;
    public $couponDiscount = 0;
    public $couponError = '';
    public $showCouponSuccess = false;

    public function updatedCheckinDate($value)
    {
        Session::put('checkin', $value);
        $this->checkinDate = $value;
    }

    public function updatedCheckoutDate($value)
    {
        Session::put('checkout', $value);
        $this->checkoutDate = $value;
    }

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

    /**
     * Apply coupon code
     */
    public function applyCoupon()
    {
        $this->couponError = '';
        $this->showCouponSuccess = false;

        if (empty($this->couponCode)) {
            $this->couponError = 'Please enter a coupon code.';
            return;
        }

        // Find the coupon
        $coupon = Coupon::byCode($this->couponCode)->first();

        if (!$coupon) {
            $this->couponError = 'Coupon code not found.';
            return;
        }

        // Validate the coupon
        if (!$coupon->isValid()) {
            $this->couponError = $coupon->getValidationMessage();
            return;
        }

        // Calculate discount
        $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));
        $originalPrice = $this->hall->price * $days;
        $discount = $coupon->calculateDiscount($originalPrice);

        $this->appliedCoupon = $coupon;
        $this->couponDiscount = $discount;
        $this->showCouponSuccess = true;
        
        // Dispatch event to update frontend
        $this->dispatch('coupon-applied', [
            'discount' => $discount,
            'couponCode' => $this->couponCode
        ]);
    }

    /**
     * Remove applied coupon
     */
    public function removeCoupon()
    {
        $this->appliedCoupon = null;
        $this->couponDiscount = 0;
        $this->couponCode = '';
        $this->couponError = '';
        $this->showCouponSuccess = false;
        
        // Dispatch event to update frontend
        $this->dispatch('coupon-removed');
    }

    /**
     * Get the final price after discount
     */
    public function getFinalPrice(): float
    {
        $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));
        $originalPrice = $this->hall->price * $days;
        
        // Apply platform discount first
        $platformDiscountedPrice = calculateRemainingAmount($originalPrice, $this->hall->hotel->online_price);
        
        // Then apply coupon discount (deducted from platform commission)
        return $platformDiscountedPrice - $this->couponDiscount;
    }

    /**
     * Get the non-refundable final price after discount
     */
    public function getNonRefundableFinalPrice(): float
    {
        $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));
        $originalPrice = $this->hall->price * $days;
        
        // Apply platform discount first
        $platformDiscountedPrice = calculateRemainingAmount($originalPrice, $this->hall->hotel->non_refundable_price);
        
        // Then apply coupon discount (deducted from platform commission)
        return $platformDiscountedPrice - $this->couponDiscount;
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
        }

        // Prevent coupon usage for non-refundable bookings
        if ($type == 'NonRefundable' && $this->appliedCoupon) {
            $details = [
                'icon' => 'error',
                'text' => 'Coupons cannot be applied to non-refundable bookings.',
                'title' => 'Coupon Not Applicable'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }

        $hall = $this->hall;
        $customer = $this->getOrCreateCustomer($this->form->getState());

        $days = calculateDateDifferenceInDays(Session::get('checkin'), Session::get('checkout'));

        $data['checked_in'] = Session::get('checkin');
        $data['checked_out'] = Session::get('checkout');
        $data['coupon_id'] = $this->appliedCoupon ? $this->appliedCoupon->id : null; // Store coupon

        if ($type == 'NonRefundable') {
            $data['discount'] = $this->hall->hotel->non_refundable_price;
            $data['due'] = calculateRemainingAmount($this->hall->price * $days, $this->hall->hotel->non_refundable_price);
            $this->due = $this->getNonRefundableFinalPrice();
        } else {
            $data['discount'] = $this->hall->hotel->online_price;
            $data['due'] = calculateRemainingAmount($this->hall->price * $days, $this->hall->hotel->online_price);
            $this->due = $this->getFinalPrice();
        }

        // booking invoice
        $invoice = BookingInvoice::create([
            'customer_id' => $customer->id,
            'hotel_id' => $this->hall->hotel_id,
            'amount' => $this->due,
            'status' => 'unpaid',
            'checked_in' => $data['checked_in'], // ensure checkin is saved
            'checked_out' => $data['checked_out'], // ensure checkout is saved
            'coupon_id' => $data['coupon_id'], // Store coupon reference
        ]);

        // Calculate and save commission amounts
        $invoice->calculateAndSaveCommission();

        $data['booking_invoice_id'] = $invoice->id;

        // create bookings
        $data['bookable_id'] = $hall->id;
        $created = $this->createBooking($hall, $data, $customer);

        // associate customer if not exist before
        $this->associateCustomer($customer->id);

        // Increment coupon usage count if coupon was applied
        if ($this->appliedCoupon && $type !== 'PayAtHotel') {
            $this->appliedCoupon->incrementUsage();
        }

        if ($type == 'PayAtHotel') {
            // Increment coupon usage for pay at hotel as well
            if ($this->appliedCoupon) {
                $this->appliedCoupon->incrementUsage();
            }
            
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
        // Prevent duplicate booking for the same hall and invoice
        $existing = Booking::where('bookable_id', $hall->id)
            ->where('booking_invoice_id', $data['booking_invoice_id'])
            ->first();

        if ($existing) {
            return $existing;
        }

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

        // Check which payment method is selected
        if ($this->selectedPaymentMethod === 'paystack') {
            // Handle Paystack payment
            try {
                $data = [
                    'email' => $booking->customer->email,
                    'amount' => $booking->amount * 100, // Paystack expects amount in kobo (100 times the naira value)
                    'reference' => $booking->reference,
                    'callback_url' => route('paystack.callback'),
                    'metadata' => [
                        'custom_fields' => [
                            [
                                'display_name' => 'Invoice ID',
                                'variable_name' => 'invoice_id',
                                'value' => $booking->id
                            ],
                            [
                                'display_name' => 'Customer',
                                'variable_name' => 'customer_id',
                                'value' => $booking->customer->id
                            ]
                        ]
                    ]
                ];

                $authorizationUrl = Paystack::getAuthorizationUrl($data)->redirectNow();
                return $authorizationUrl;
            } catch (\Exception $e) {
                // Log the error for debugging
                Log::error('Paystack payment initiation failed', [
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
        } else {
            // Default to Opay payment
            $paymentData = [
                'reference' => strtoupper($booking->reference),
                'amount' => $booking->amount, // Opay expects actual amount
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
                Log::error('OPay payment initiation failed', [
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
        $this->checkinDate = Session::get('checkin');
        $this->checkoutDate = Session::get('checkout');

        $this->hotel = $this->hall->hotel;

        // Load payment methods
        $paymentGatewayService = new PaymentGatewayService();
        $this->paymentMethods = $paymentGatewayService->getActivePaymentMethods();
        $this->selectedPaymentMethod = $this->paymentMethods->first()->code;
    }

    public function refreshBookingData()
    {
        // This method will be called from the frontend to refresh the component
        // when dates are updated, ensuring calculations are current
        $this->dispatch('$refresh');
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        return view('livewire.book-hall');
    }
}