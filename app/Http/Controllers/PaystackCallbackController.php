<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Booking;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Services\PaystackService;
use App\Models\BookingInvoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaystackCallbackController extends Controller
{
    public function __construct(protected PaystackService $paystackService) {}

    /**
     * Handle Paystack payment callback
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function handleGatewayCallback(Request $request)
    {
        Log::info('Paystack callback received', $request->all());

        // Get the reference from the request or Paystack facade
        $paymentDetails = Paystack::getPaymentData();
        $reference = $paymentDetails['data']['reference'] ?? $request->reference;

        if (!$reference) {
            Log::error('Paystack callback: No reference provided');
            return redirect()->route('booking.failed')
                ->with('error', 'Payment reference not found in callback.');
        }

        try {
            Log::info('Paystack payment verification result', [
                'reference' => $reference,
                'payment_details' => $paymentDetails
            ]);

            // Check if payment was successful
            if ($this->isPaymentSuccessful($paymentDetails)) {
                return $this->handleSuccessfulPayment($reference);
            } else {
                return $this->handleFailedPayment($reference, $paymentDetails);
            }
        } catch (Exception $e) {
            Log::error('Paystack payment verification error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('booking.failed')
                ->with('error', 'Error verifying payment: ' . $e->getMessage());
        }
    }

    public function handleGatewayReturn(Request $request, $reference = null)
    {
        Log::info('Paystack return received', $request->all());

        $reference = $reference ?? $request->reference;

        if (!$reference) {
            return redirect()->route('booking.failed')
                ->with('error', 'Payment reference not found.');
        }

        return $this->handleSuccessfulPayment($reference);
    }

    /**
     * Check if payment is successful based on Paystack response
     *
     * @param array $paymentDetails
     * @return bool
     */
    private function isPaymentSuccessful(array $paymentDetails): bool
    {
        // Check Paystack specific conditions for successful payment
        $statusChecks = [
            isset($paymentDetails['status']) && $paymentDetails['status'] === true,
            isset($paymentDetails['data']['status']) && strtolower($paymentDetails['data']['status']) === 'success'
        ];

        return in_array(true, $statusChecks);
    }

    /**
     * Handle successful payment
     *
     * @param string $reference
     * @return RedirectResponse
     */
    private function handleSuccessfulPayment(string $reference): RedirectResponse
    {
        try {
            // Find the booking invoice
            $bookingInvoice = BookingInvoice::where('reference', $reference)->first();

            if (!$bookingInvoice) {
                Log::error('Booking invoice not found', ['reference' => $reference]);
                return redirect()->route('booking.failed')
                    ->with('error', 'Booking not found for reference: ' . $reference);
            }

            // Check if already processed to avoid duplicate processing
            if ($bookingInvoice->status === 'paid') {
                Log::info('Payment already processed', ['reference' => $reference]);
                return redirect()->route('booking.success')
                    ->with('success', 'Payment already confirmed for booking ' . $reference);
            }

            // Update the booking invoice
            $bookingInvoice->update([
                'status' => 'paid',
                'payed_on' => now()
            ]);

            // Calculate and save commission amounts
            if (method_exists($bookingInvoice, 'calculateAndSaveCommission')) {
                $bookingInvoice->calculateAndSaveCommission();
            }

            $bookingInvoice->loadCount('bookings'); // Convert to actual amount if needed
            // Update all associated bookings (status sync handled in Booking observer/event)
            $bookings = Booking::where('booking_invoice_id', $bookingInvoice->id)->get();
            foreach ($bookings as $booking) {
                $booking->update([
                    'status' => 'paid',
                    'amount_paid' => (int) ($bookingInvoice->amount / $bookingInvoice->bookings_count),
                ]);
            }

            Log::info('Payment successfully processed', [
                'reference' => $reference,
                'booking_invoice_id' => $bookingInvoice->id
            ]);

            // Send payment confirmation emails
            try {
                $bookingInvoice->sendPaymentEmails();
                Log::info('Payment confirmation emails sent', ['reference' => $reference]);
            } catch (Exception $e) {
                Log::warning('Failed to send payment emails', [
                    'reference' => $reference,
                    'error' => $e->getMessage()
                ]);
            }

            // Redirect to success page
            return redirect()->route('booking.success')
                ->with('success', 'Payment successful for booking ' . $reference);
        } catch (Exception $e) {
            Log::error('Error processing successful payment', [
                'reference' => $reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('booking.failed')
                ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle failed payment
     *
     * @param string $reference
     * @param array $paymentDetails
     * @return RedirectResponse
     */
    private function handleFailedPayment(string $reference, array $paymentDetails): RedirectResponse
    {
        Log::error('Payment verification failed', [
            'reference' => $reference,
            'payment_details' => $paymentDetails
        ]);

        return redirect()->route('booking.failed')
            ->with('error', 'Payment verification failed for booking ' . $reference);
    }
}
