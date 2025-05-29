<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Booking;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Services\OpayService;
use App\Models\BookingInvoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

class OpayCallbackController extends Controller
{
    public function __construct(protected OpayService $opayService) {}

    /**
     * Handle OPay payment callback
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function handleGatewayCallback(Request $request)
    {
        Log::info('OPay callback received', $request->all());

        // Get the reference from the request
        $reference = $request->query('reference') ?? Cache::get('reference');

        if (!$reference) {
            Log::error('OPay callback: No reference provided');
            return redirect()->route('booking.failed')
                ->with('error', 'Payment reference not found in callback.');
        }

        try {
            // Verify the payment with OPay
            $paymentDetails = $this->opayService->verifyPayment($reference);

            Log::info('OPay payment verification result', [
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
            Log::error('OPay payment verification error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('booking.failed')
                ->with('error', 'Error verifying payment: ' . $e->getMessage());
        }
    }

    public function handleGatewayReturn(Request $request, $reference)
    {
        Log::info('OPay return received', $request->all());

        return $this->handleSuccessfulPayment($reference);

        // return view('booking.success')
        //     ->with('success', 'Payment successful. Thank you for your booking!');
    }

    // public function handleGatewayCallback(Request $request)
    // {
    //     $opayService = new OpayService();
    //     $reference = $request->query('reference'); // OPay typically returns reference in query params

    //     if ($reference) {
    //         try {
    //             $paymentDetails = $opayService->verifyPayment($reference);

    //             Log::info('Payment verification initiated', [
    //                 'reference' => $reference,
    //                 'payment_details' => $paymentDetails
    //             ]);

    //             if ($paymentDetails['status'] === 'success' && $paymentDetails['payment_status'] === 'SUCCESS') {
    //                 // Payment was successful, update your invoice
    //                 $bookingInvoice = BookingInvoice::where('reference', $reference)->first();
    //                 if ($bookingInvoice) {
    //                     // Update the invoice and associated bookings
    //                     $bookingInvoice->update(['status' => 'paid', 'date_paid' => now()]);

    //                     // Update all associated bookings
    //                     Booking::where('booking_invoice_id', $bookingInvoice->id)
    //                         ->update(['status' => 'paid', 'date_paid' => now()]);

    //                     Log::info('Payment successful', [
    //                         'reference' => $reference,
    //                         'booking_invoice_id' => $bookingInvoice->id
    //                     ]);
    //                 }

    //                 // Redirect to a success page with flash message
    //                 return redirect()->route('booking.success')
    //                     ->with('success', 'Payment successful for booking ' . $reference);
    //             } else {
    //                 Log::error('Payment verification failed', [
    //                     'reference' => $reference,
    //                     'payment_details' => $paymentDetails ?? "nothing returned"
    //                 ]);

    //                 // Redirect to a failure page with flash message
    //                 return redirect()->route('booking.failed')
    //                     ->with('error', 'Payment verification failed for booking ' . $reference);
    //             }
    //         } catch (\Exception $e) {
    //             Log::error('Payment verification error', [
    //                 'reference' => $reference,
    //                 'error' => $e->getMessage()
    //             ]);

    //             // Redirect to a failure page with flash message
    //             return redirect()->route('booking.failed')
    //                 ->with('error', 'Error verifying payment: ' . $e->getMessage());
    //         }
    //     } else {
    //         Log::error('Payment callback without reference');

    //         // Redirect to a failure page with flash message
    //         return redirect()->route('booking.failed')
    //             ->with('error', 'Payment reference not found in callback.');
    //     }
    // }

    /**
     * Check if payment is successful based on OPay response
     *
     * @param array $paymentDetails
     * @return bool
     */
    private function isPaymentSuccessful(array $paymentDetails): bool
    {
        // Check multiple conditions for successful payment
        $statusChecks = [
            $paymentDetails['status'] === 'success',
            isset($paymentDetails['payment_status']) && strtoupper($paymentDetails['payment_status']) === 'SUCCESS',
            isset($paymentDetails['data']['status']) && strtoupper($paymentDetails['data']['status']) === 'SUCCESS'
        ];

        return in_array(true, $statusChecks);
    }

    /**
     * Handle successful payment
     *
     * @param string $reference
     * @param array $paymentDetails
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
            $bookingInvoice->loadCount('bookings'); // Convert to actual amount if needed
            // Update all associated bookings
            $updatedBookings = Booking::where('booking_invoice_id', $bookingInvoice->id)
                ->update([
                    'status' => 'paid',
                    'amount_paid' => (int) ($bookingInvoice->amount / $bookingInvoice->bookings_count),
                ]);

            Log::info('Payment successfully processed', [
                'reference' => $reference,
                'booking_invoice_id' => $bookingInvoice->id,
                'updated_bookings' => $updatedBookings,
                'amount' => $bookingInvoice->amount
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
                // Don't fail the whole process if email fails
            }

            return redirect()->route('booking.success')
                ->with('success', 'Payment successful for booking ' . $reference . '. Confirmation email sent.');
        } catch (Exception $e) {
            Log::error('Error processing successful payment', [
                'reference' => $reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('booking.failed')
                ->with('error', 'Error processing payment confirmation: ' . $e->getMessage());
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
        Log::warning('Payment failed or pending', [
            'reference' => $reference,
            'payment_details' => $paymentDetails
        ]);

        // You might want to update the booking status to 'failed' or 'pending'
        $bookingInvoice = BookingInvoice::where('reference', $reference)->first();

        if ($bookingInvoice) {
            $paymentStatus = $paymentDetails['payment_status'] ?? 'unknown';

            // Update status based on payment result
            if (in_array(strtoupper($paymentStatus), ['FAILED', 'CANCELLED', 'EXPIRED'])) {
                $bookingInvoice->update(['status' => 'failed']);

                // Also update associated bookings
                Booking::where('booking_invoice_id', $bookingInvoice->id)
                    ->update(['status' => 'failed']);
            }
        }

        $errorMessage = $this->getPaymentErrorMessage($paymentDetails);

        return redirect()->route('booking.failed')
            ->with('error', $errorMessage);
    }

    /**
     * Get appropriate error message based on payment details
     *
     * @param array $paymentDetails
     * @return string
     */
    private function getPaymentErrorMessage(array $paymentDetails): string
    {
        $paymentStatus = strtoupper($paymentDetails['payment_status'] ?? 'UNKNOWN');

        switch ($paymentStatus) {
            case 'PENDING':
                return 'Payment is still being processed. Please wait a few minutes and contact support if the issue persists.';
            case 'FAILED':
                return 'Payment failed. Please check your payment details and try again.';
            case 'CANCELLED':
                return 'Payment was cancelled. You can try making the payment again.';
            case 'EXPIRED':
                return 'Payment session expired. Please try making the payment again.';
            case 'INSUFFICIENT_FUNDS':
                return 'Insufficient funds. Please check your account balance and try again.';
            default:
                return 'Payment could not be completed. Please try again or contact support.';
        }
    }

    /**
     * Handle webhook callbacks (optional - for server-to-server notifications)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        Log::info('OPay webhook received', $request->all());

        try {
            $payload = $request->all();
            $webhookData = $this->opayService->handleWebhook($payload);

            if ($webhookData['status'] === 'success' && $webhookData['reference']) {
                // Process the webhook similar to callback
                $this->processWebhookPayment($webhookData);
            }

            return response()->json(['status' => 'success'], 200);
        } catch (Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Process webhook payment notification
     *
     * @param array $webhookData
     * @return void
     */
    private function processWebhookPayment(array $webhookData): void
    {
        $reference = $webhookData['reference'];

        if ($webhookData['payment_status'] === 'SUCCESS') {
            $bookingInvoice = BookingInvoice::where('reference', $reference)->first();

            if ($bookingInvoice && $bookingInvoice->status !== 'paid') {
                $bookingInvoice->update([
                    'status' => 'paid',
                    'date_paid' => now(),
                    'payment_method' => 'opay'
                ]);

                Booking::where('booking_invoice_id', $bookingInvoice->id)
                    ->update(['status' => 'paid', 'date_paid' => now()]);

                Log::info('Webhook payment processed', ['reference' => $reference]);
            }
        }
    }
}