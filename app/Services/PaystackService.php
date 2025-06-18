<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaystackService
{
    private $secretKey;
    private $publicKey;
    private $paymentUrl;

    public function __construct()
    {
        $this->secretKey = config('paystack.secretKey');
        $this->publicKey = config('paystack.publicKey');
        $this->paymentUrl = config('paystack.paymentUrl');
    }

    /**
     * Initialize a Paystack transaction
     *
     * @param array $paymentData
     * @return array
     * @throws Exception
     */
    public function initializeTransaction(array $paymentData)
    {
        try {
            $data = [
                'amount' => $paymentData['amount'] * 100, // Convert to kobo
                'reference' => $paymentData['reference'],
                'email' => $paymentData['customer_email'],
                'callback_url' => $paymentData['callback_url'],
                'metadata' => [
                    'custom_fields' => [
                        [
                            'display_name' => 'Customer Name',
                            'variable_name' => 'customer_name',
                            'value' => $paymentData['customer_name']
                        ],
                        [
                            'display_name' => 'Product',
                            'variable_name' => 'product',
                            'value' => $paymentData['product_name']
                        ]
                    ]
                ]
            ];

            // Initialize transaction
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->paymentUrl . '/transaction/initialize', $data);

            if ($response->successful()) {
                $responseData = $response->json();

                if ($responseData['status']) {
                    // Log::info('Paystack transaction initialized successfully', [
                    //     'reference' => $paymentData['reference'],
                    //     'auth_url' => $responseData['data']['authorization_url']
                    // ]);

                    return [
                        'status' => 'success',
                        'data' => $responseData['data'],
                        'authorization_url' => $responseData['data']['authorization_url'],
                        'access_code' => $responseData['data']['access_code'],
                        'reference' => $responseData['data']['reference']
                    ];
                } else {
                    Log::error('Paystack transaction initialization failed', [
                        'reference' => $paymentData['reference'],
                        'error' => $responseData['message']
                    ]);

                    throw new Exception($responseData['message'] ?? 'Payment initialization failed');
                }
            } else {
                Log::error('Paystack API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                throw new Exception('Failed to connect to Paystack API');
            }
        } catch (Exception $e) {
            Log::error('Paystack transaction initialization error', [
                'reference' => $paymentData['reference'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Verify Paystack transaction
     *
     * @param string $reference
     * @return array
     * @throws Exception
     */
    public function verifyTransaction(string $reference)
    {
        try {
            // Using the SDK to verify
            $paymentDetails = Paystack::getPaymentData();

            if ($paymentDetails['status'] && $paymentDetails['data']['status'] === 'success') {
                // Log::info('Paystack payment verification successful', [
                //     'reference' => $reference,
                //     'status' => $paymentDetails['data']['status']
                // ]);

                return [
                    'status' => 'success',
                    'data' => $paymentDetails['data'],
                    'payment_status' => $paymentDetails['data']['status'],
                    'amount' => $paymentDetails['data']['amount'] / 100, // Convert back from kobo
                    'reference' => $paymentDetails['data']['reference']
                ];
            } else {
                // Log::error('Paystack payment verification failed', [
                //     'reference' => $reference,
                //     'error' => $paymentDetails['message'] ?? 'Unknown error'
                // ]);

                throw new Exception($paymentDetails['message'] ?? 'Payment verification failed');
            }
        } catch (Exception $e) {
            Log::error('Paystack payment verification error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Alternative verification method using HTTP directly
     *
     * @param string $reference
     * @return array
     * @throws Exception
     */
    public function verifyTransactionHttp(string $reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->paymentUrl . '/transaction/verify/' . $reference);

            if ($response->successful()) {
                $responseData = $response->json();

                if ($responseData['status'] && $responseData['data']['status'] === 'success') {
                    // Log::info('Paystack payment verification successful', [
                    //     'reference' => $reference,
                    //     'status' => $responseData['data']['status']
                    // ]);

                    return [
                        'status' => 'success',
                        'data' => $responseData['data'],
                        'payment_status' => $responseData['data']['status'],
                        'amount' => $responseData['data']['amount'] / 100, // Convert back from kobo
                        'reference' => $responseData['data']['reference']
                    ];
                } else {
                    // Log::error('Paystack payment verification failed', [
                    //     'reference' => $reference,
                    //     'error' => $responseData['message'] ?? 'Unknown error'
                    // ]);

                    throw new Exception($responseData['message'] ?? 'Payment verification failed');
                }
            } else {
                // Log::error('Paystack verification API request failed', [
                //     'status' => $response->status(),
                //     'response' => $response->body()
                // ]);

                throw new Exception('Failed to connect to Paystack API for verification');
            }
        } catch (Exception $e) {
            Log::error('Paystack payment verification error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}