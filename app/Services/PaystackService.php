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
                    throw new Exception($responseData['message'] ?? 'Payment initialization failed');
                }
            } else {
                throw new Exception('Failed to connect to Paystack API');
            }
        } catch (Exception $e) {
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

            // Convert to array if needed
            $data = is_array($paymentDetails) ? $paymentDetails : (array)$paymentDetails;

            if (
                isset($data['status']) && $data['status'] &&
                isset($data['data']['status']) && $data['data']['status'] === 'success'
            ) {

                return [
                    'status' => 'success',
                    'data' => $data['data'],
                    'payment_status' => $data['data']['status'],
                    'amount' => $data['data']['amount'] / 100, // Convert back from kobo
                    'reference' => $data['data']['reference']
                ];
            } else {
                throw new Exception($data['message'] ?? 'Payment verification failed');
            }
        } catch (Exception $e) {
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
                    return [
                        'status' => 'success',
                        'data' => $responseData['data'],
                        'payment_status' => $responseData['data']['status'],
                        'amount' => $responseData['data']['amount'] / 100, // Convert back from kobo
                        'reference' => $responseData['data']['reference']
                    ];
                } else {
                    throw new Exception($responseData['message'] ?? 'Payment verification failed');
                }
            } else {
                throw new Exception('Failed to connect to Paystack API for verification');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
