<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\OpayService;
use App\Models\BookingInvoice;
use Livewire\Attributes\Layout;
use App\Services\PaymentGatewayService;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PayNow extends Component
{
    public BookingInvoice $invoice;
    public $selectedPaymentMethod;
    public $paymentMethods;

    public function mount()
    {
        $paymentGatewayService = new PaymentGatewayService();
        $this->paymentMethods = $paymentGatewayService->getActivePaymentMethods();
        $this->selectedPaymentMethod = $this->paymentMethods->first()->code ?? 'opay';
    }

    public function payNow()
    {
        $booking = $this->invoice;
        $reference = $booking->reference;

        // Get payment method code
        $paymentMethodCode = $this->selectedPaymentMethod;

        $paymentData = [
            'reference' => strtoupper($reference),
            'amount' => $booking->amount,
            'currency' => 'NGN',
            'callback_url' => route($paymentMethodCode . '.callback'),
            'return_url' => route($paymentMethodCode . '.return', $reference),
            'country' => 'NG',
            'customer_name' => $booking->customer->firstname . ' ' . $booking->customer->lastname,
            'customer_email' => $booking->customer->email,
            'customer_phone' => $booking->customer->phone ?? '',
            'product_name' => 'Hotel Booking - ' . ucfirst($booking->hotel->name),
            'product_description' => 'Booking payment for ' . ucfirst($booking->hotel->name) . ' (Ref: ' . $reference . ')',
        ];

        try {
            $paymentGatewayService = new PaymentGatewayService();
            $response = $paymentGatewayService->processPayment($paymentMethodCode, $paymentData);

            if ($response['status'] === 'success') {
                Cache::put($reference, $booking->reference, now()->addMinutes(30));

                // Redirect based on payment method
                if ($paymentMethodCode === 'opay' && isset($response['cashier_url'])) {
                    return redirect()->away($response['cashier_url']);
                } elseif ($paymentMethodCode === 'paystack' && isset($response['authorization_url'])) {
                    return redirect()->away($response['authorization_url']);
                } else {
                    throw new \Exception('Payment URL not found in response');
                }
            } else {
                throw new \Exception('Failed to initialize payment');
            }
        } catch (\Exception $e) {
            // Show error using sweet alert (consistent with BookRoom)
            $details = [
                'icon' => 'error',
                'text' => $e->getMessage(),
                'title' => 'Payment Failed'
            ];

            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    #[Layout('components.layouts.frontend')]
    public function render()
    {
        return view('livewire.pay-now', [
            'invoice' => $this->invoice,
            'paymentMethods' => $this->paymentMethods,
        ]);
    }
}
