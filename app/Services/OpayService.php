<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpayService
{
    private $baseUrl;
    private $secretKey;
    private $publicKey;
    private $merchantId;

    public function __construct()
    {
        $this->baseUrl = config('opay.base_url', 'https://testapi.opaycheckout.com/api/v1/');
        $this->secretKey = config('opay.secret_key');
        $this->publicKey = config('opay.public_key');
        $this->merchantId = config('opay.merchant_id');
    }

    /**
     * Create OPay Express Checkout payment
     *
     * @param array $paymentData
     * @return array
     * @throws Exception
     */
    public function createExpressCheckout(array $paymentData)
    {
        try {
            $endpoint = '/international/cashier/create';
            $url = $this->baseUrl . $endpoint;

            // Prepare the payment data
            $data = [
                'reference' => $paymentData['reference'],
                'amount' => [
                    'currency' => $paymentData['currency'] ?? 'NGN',
                    'total' => $paymentData['amount'] * 100 // Convert to kobo
                ],
                'callbackUrl' => $paymentData['callback_url'],
                'returnUrl' => $paymentData['return_url'],
                'country' => $paymentData['country'] ?? 'NG',
                'userInfo' => [
                    'userName' => $paymentData['customer_name'],
                    'userEmail' => $paymentData['customer_email'],
                    'userMobile' => $paymentData['customer_phone'] ?? ''
                ],
                'product' => [
                    'name' => $paymentData['product_name'],
                    'description' => $paymentData['product_description'] ?? $paymentData['product_name']
                ]
            ];

            // Add optional payment method if specified
            if (isset($paymentData['pay_method'])) {
                $data['payMethod'] = $paymentData['pay_method'];
            }

            // Generate signature
            // $signature = $this->generateSignature($data);

            $signature = $this->publicKey;
            // Prepare headers
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $signature,
                'MerchantId' => $this->merchantId
            ];

            // Make the API request
            $response = Http::withHeaders($headers)->post($url, $data);

            if ($response->successful()) {
                $responseData = $response->json();

                if ($responseData['code'] === '00000') {
                    // Log::info('OPay Express Checkout created successfully', [
                    //     'reference' => $paymentData['reference'],
                    //     'cashier_url' => $responseData['data']['cashierUrl']
                    // ]);

                    return [
                        'status' => 'success',
                        'data' => $responseData['data'],
                        'cashier_url' => $responseData['data']['cashierUrl'],
                        'reference' => $responseData['data']['reference']
                    ];
                } else {
                    Log::error('OPay Express Checkout failed', [
                        'reference' => $paymentData['reference'],
                        'error' => $responseData['message']
                    ]);

                    throw new Exception($responseData['message'] ?? 'Payment creation failed');
                }
            } else {
                Log::error('OPay API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                throw new Exception('Failed to connect to OPay API');
            }
        } catch (Exception $e) {
            Log::error('OPay Express Checkout error', [
                'reference' => $paymentData['reference'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Verify payment status
     *
     * @param string $reference
     * @return array
     * @throws Exception
     */
    public function verifyPayment(string $reference)
    {
        try {
            $endpoint = '/international/transaction/status';
            $url = $this->baseUrl . $endpoint;

            $data = [
                'reference' => $reference,
                'country' => 'NG'
            ];

            // Generate signature
            $signature = $this->generateSignature($data);

            // Prepare headers
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $signature,
                'MerchantId' => $this->merchantId
            ];

            // Make the API request
            $response = Http::withHeaders($headers)->post($url, $data);


            // log response
            Log::info('OPay payment verification request', [
                'reference' => $reference,
                'response' => $response,
                'response_status' => $response->status(),
                'response_body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                if ($responseData['code'] === '00000') {
                    Log::info('OPay payment verification successful', [
                        'reference' => $reference,
                        'status' => $responseData['data']['status']
                    ]);

                    return [
                        'status' => 'success',
                        'data' => $responseData['data'],
                        'payment_status' => $responseData['data']['status'],
                        'amount' => $responseData['data']['amount']
                    ];
                } else {
                    Log::error('OPay payment verification failed', [
                        'reference' => $reference,
                        'error' => $responseData['message']
                    ]);

                    throw new Exception($responseData['message'] ?? 'Payment verification failed');
                }
            } else {
                Log::error('OPay verification API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                throw new Exception('Failed to connect to OPay API for verification');
            }
        } catch (Exception $e) {
            Log::error('OPay payment verification error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Handle webhook callback
     *
     * @param array $payload
     * @return array
     */
    public function handleWebhook(array $payload)
    {
        try {
            Log::info('OPay webhook received', $payload);

            // Verify webhook signature if needed
            // $this->verifyWebhookSignature($payload);

            return [
                'status' => 'success',
                'reference' => $payload['reference'] ?? null,
                'payment_status' => $payload['status'] ?? null,
                'amount' => $payload['amount'] ?? null
            ];
        } catch (Exception $e) {
            Log::error('OPay webhook handling error', [
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Generate signature for OPay API requests
     *
     * @param array $data
     * @return string
     */
    private function generateSignature(array $data)
    {
        // Convert data to JSON and create signature
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha512', $jsonData, $this->secretKey);

        return $signature;
    }

    /**
     * Verify webhook signature
     *
     * @param array $payload
     * @return bool
     * @throws Exception
     */
    private function verifyWebhookSignature(array $payload)
    {
        // Implementation depends on OPay's webhook signature verification method
        // This is a placeholder - check OPay documentation for exact implementation
        return true;
    }
}
