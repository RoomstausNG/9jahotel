<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Services\OpayService;
use App\Models\BookingInvoice;
use Illuminate\Support\Facades\Log;

class OpayPaymentForm extends Component
{
    public BookingInvoice $booking;

    public $paymentMethod = 'card'; // Default payment method
    public $amount = 1000; // Example amount, you'll likely pass this in or set it dynamically
    public $currency = 'NGN';
    public $country = 'NG';
    public $reason = 'Payment for hotel booking';
    public $customerEmail = ''; // Collect from user
    public $customerPhone = ''; // Collect from user

    // Card details
    public $firstName = '';
    public $lastName = '';
    public $cardNumber = '';
    public $cardDateMonth = '';
    public $cardDateYear = '';
    public $cardCVC = '';
    public $return3dsUrl = ''; // You'll need to define a route for this
    public $callbackUrl = ''; // And a route for this too
    public $expiresAt = ''; // e.g., '5m' for 5 minutes

    // Bank Account Details (example, might not be needed if using OPay's hosted page for this)
    public $bankAccountNumber = '';
    public $bankCode = '';
    public $bvn = '';
    public $dobDay = '';
    public $dobMonth = '';
    public $dobYear = '';

    // Token Details (if applicable)
    public $token = '';

    public $paymentReference;
    public $paymentResponse;
    public $errorMessage;

    protected $opayService;

    public function mount(OpayService $opayService)
    {
        $this->opayService = $opayService;
        $this->paymentReference = 'WKL-' . strtoupper(Str::random(12)); // Generate a unique reference
        $this->return3dsUrl = route('opay.payment.callback'); // Example route, create this
        $this->callbackUrl = route('opay.payment.webhook'); // Example route, create this
        $this->expiresAt = now()->addMinutes(15)->toIso8601String(); // Example: expires in 15 mins
    }

    public function rules()
    {
        $rules = [
            'paymentMethod' => 'required|string|in:card,bank_transfer,ussd,token',
            'amount' => 'required|numeric|min:100', // Min amount might vary
            'customerEmail' => 'required|email',
            'customerPhone' => 'required|string',
            'reason' => 'required|string|max:255',
        ];

        if ($this->paymentMethod === 'card') {
            $rules = array_merge($rules, [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'cardNumber' => 'required|string|digits_between:16,19',
                'cardDateMonth' => 'required|string|digits:2',
                'cardDateYear' => 'required|string|digits:4',
                'cardCVC' => 'required|string|digits_between:3,4',
            ]);
        }
        // Add rules for 'bank_transfer', 'ussd', 'token' if they have specific fields
        // For bank transfer, you might need bankAccountNumber, bankCode, bvn, dobDay, etc.
        // For token payments, you'd need the token itself.

        return $rules;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function processPayment()
    {
        $this->validate();
        $this->errorMessage = null;
        $this->paymentResponse = null;

        try {
            switch ($this->paymentMethod) {
                case 'card':
                    $this->paymentResponse = $this->opayService->initializeCardTransaction(
                        $this->paymentReference,
                        $this->amount,
                        $this->firstName,
                        $this->lastName,
                        $this->customerEmail,
                        $this->cardNumber,
                        $this->cardDateMonth,
                        $this->cardDateYear,
                        $this->cardCVC,
                        $this->return3dsUrl,
                        $this->bankAccountNumber, // This might not be needed for card, check OPay docs
                        $this->bankCode,          // This might not be needed for card, check OPay docs
                        $this->reason,
                        $this->callbackUrl,
                        $this->expiresAt,
                        null,
                        null,
                        null,
                        null,
                        null, // Billing details (optional)
                        $this->currency,
                        $this->country
                    );
                    break;

                case 'bank_transfer':
                    // Ensure you have all required fields like bvn, dobDay, dobMonth, dobYear
                    $this->paymentResponse = $this->opayService->initializeBankAccountTransaction(
                        $this->paymentReference,
                        $this->amount,
                        $this->customerPhone,
                        $this->return3dsUrl, // May need a different URL or handling
                        $this->bankAccountNumber,
                        $this->bankCode,
                        $this->reason,
                        $this->bvn,
                        $this->dobDay,
                        $this->dobMonth,
                        $this->dobYear,
                        $this->currency,
                        $this->country
                    );
                    break;

                case 'token':
                    $this->paymentResponse = $this->opayService->initializeTokenTransaction(
                        $this->paymentReference,
                        $this->amount,
                        $this->customerPhone,
                        $this->customerEmail,
                        $this->reason,
                        $this->callbackUrl,
                        $this->expiresAt,
                        $this->token, // The actual token for payment
                        $this->currency,
                        $this->country
                    );
                    break;

                case 'ussd':
                    // USSD might have a different flow or method in the OPay package.
                    // For example: $this->paymentResponse = Opay::ussd()->generateCode(...);
                    // This is a placeholder, consult Triverla/laravel-opay documentation.
                    $this->errorMessage = 'USSD payment method not fully implemented yet.';
                    Log::warning('Attempted USSD payment - not implemented in OpayPaymentForm.php');
                    break;
            }

            if ($this->paymentResponse) {
                // Handle successful API call from OpayService
                // This response might contain a redirect URL, or data to display (like USSD code)
                Log::info('OPay API call successful', ['reference' => $this->paymentReference, 'response' => $this->paymentResponse]);
                // If there's a redirect URL in the response, you might want to emit an event or redirect directly
                // e.g., if (isset($this->paymentResponse['data']['authorization_url'])) {
                //    return redirect($this->paymentResponse['data']['authorization_url']);
                // }
                session()->flash('payment_success', 'Payment initiated successfully! Check response.');
                // Potentially redirect or show next steps based on $this->paymentResponse
            } elseif (!$this->errorMessage) {
                $this->errorMessage = 'Payment initiation failed. Please try again.';
            }

        } catch (\Exception $e) {
            Log::error('Error processing payment: ' . $e->getMessage(), ['reference' => $this->paymentReference, 'method' => $this->paymentMethod]);
            $this->errorMessage = 'An unexpected error occurred: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.opay-payment-form');
    }
}
