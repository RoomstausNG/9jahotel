<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpayService;
use Illuminate\Support\Facades\Log;

class OpayPaymentController extends Controller
{

    public function __construct(protected OpayService $opayService)
    {
    }

    /**
     * Handle the callback from OPay after a payment attempt (e.g., 3DS redirect).
     */
    public function handleCallback(Request $request)
    {
        Log::info('OPay Callback Received:', $request->all());

        // The request content will depend on OPay's specific callback structure.
        // You might get a transaction reference or status.
        $transactionReference = $request->input('trxref') ?? $request->input('reference'); // Example keys
        $orderNo = $request->input('orderNo'); // Or however OPay sends it

        if ($transactionReference && $orderNo) {
            $statusResponse = $this->opayService->checkTransactionStatus($transactionReference, $orderNo);
            Log::info('OPay Callback - Transaction Status Checked:', (array) $statusResponse);

            // Based on $statusResponse, update your order, notify the user, and redirect them
            // to a success or failure page.
            // For example:
            // if (isset($statusResponse['data']['status']) && $statusResponse['data']['status'] === 'SUCCESSFUL') {
            //     return redirect()->route('payment.success.page')->with('message', 'Payment confirmed!');
            // } else {
            //     return redirect()->route('payment.failure.page')->with('error', 'Payment failed or pending.');
            // }
        }
        // Fallback redirect if essential info is missing
        return redirect()->route('home')->with('info', 'Processing payment information.');
    }

    /**
     * Handle incoming webhooks from OPay.
     */
    public function handleWebhook(Request $request)
    {
        Log::info('OPay Webhook Received:', $request->all());

        $payload = $request->all();

        // It's CRITICAL to verify the webhook signature here to ensure it's from OPay.
        // The Triverla/laravel-opay package might have a helper for this.
        // Example (hypothetical - check package docs):
        // $signature = $request->header('X-Opay-Signature');
        // if (!$this->opayService->isValidWebhookSignature($payload, $signature)) {
        //     Log::warning('Invalid OPay webhook signature.');
        //     return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        // }

        $this->opayService->handleWebhook($payload);

        return response()->json(['status' => 'success']);
    }
}
