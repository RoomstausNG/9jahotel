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
     */    public function handleGatewayCallback(Request $request)
    {
        // Get the reference from the request or Paystack facade
        $paymentDetails = Paystack::getPaymentData();
        $reference = $paymentDetails['data']['reference'] ?? $request->reference;

        if (!$reference) {
            return redirect()->route('booking.failed')
                ->with('error', 'Payment reference not found in callback.');
        }

        try {
            // Convert Paystack response to array if needed
            $paymentDetailsArray = is_array($paymentDetails) ? $paymentDetails : (array)$paymentDetails;

            // Check if payment was successful
            if ($this->isPaymentSuccessful($paymentDetailsArray)) {
                return $this->handleSuccessfulPayment($reference);
            } else {
                return $this->handleFailedPayment($reference, $paymentDetailsArray);
            }
        } catch (Exception $e) {
            return redirect()->route('booking.failed')
                ->with('error', 'Error verifying payment: ' . $e->getMessage());
        }
    }

    public function handleGatewayReturn(Request $request, $reference = null)
    {
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
                return redirect()->route('booking.failed')
                    ->with('error', 'Booking not found for reference: ' . $reference);
            }

            // Check if already processed to avoid duplicate processing
            if ($bookingInvoice->status === 'paid') {
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

            // Send payment confirmation emails
            try {
                $bookingInvoice->sendPaymentEmails();
            } catch (Exception $e) {
                // Failed to send emails, but payment was successful, so continue
            }

            // Redirect to success page
            return redirect()->route('booking.success')
                ->with('success', 'Payment successful for booking ' . $reference);
        } catch (Exception $e) {
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
        return redirect()->route('booking.failed')
            ->with('error', 'Payment verification failed for booking ' . $reference);
    }
}
