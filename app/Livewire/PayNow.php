<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\OpayService;
use App\Models\BookingInvoice;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PayNow extends Component
{
    public BookingInvoice $invoice;

    public function payNow()
    {
        $booking = $this->invoice;
        $reference = $booking->reference;

        $paymentData = [
            'reference' => strtoupper($reference),
            'amount' => $booking->amount,
            'currency' => 'NGN',
            'callback_url' => route('opay.callback'),
            'return_url' => route('opay.return', $reference),
            'country' => 'NG',
            'customer_name' => $booking->customer->firstname . ' ' . $booking->customer->lastname,
            'customer_email' => $booking->customer->email,
            'customer_phone' => $booking->customer->phone ?? '',
            'product_name' => 'Hotel Booking - ' . ucfirst($booking->hotel->name),
            'product_description' => 'Booking payment for ' . ucfirst($booking->hotel->name) . ' (Ref: ' . $reference . ')',
        ];

        dd($paymentData);

        try {
            $opayService = new OpayService();
            $response = $opayService->createExpressCheckout($paymentData);

            dd($response);

            Log::info('OPay payment initiation response', [
                'reference' => $reference,
                'response' => $response
            ]);

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
        ]);
    }
}
