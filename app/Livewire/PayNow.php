<?php

namespace App\Livewire;

use App\Models\BookingInvoice;
use App\Services\OpayService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PayNow extends Component
{
    public BookingInvoice $invoice;

    public function pay()
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
            \Log::error('OPay payment initiation failed', [
                'booking_id' => $booking->id,
                'reference' => $booking->reference,
                'error' => $e->getMessage()
            ]);

            session()->flash('error', $e->getMessage());
            return null;
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