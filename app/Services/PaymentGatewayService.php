<?php

namespace App\Services;

use App\Models\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
  /**
   * Get active payment methods
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getActivePaymentMethods()
  {
    return PaymentMethod::where('is_active', true)->get();
  }

  /**
   * Process payment using the specified payment method
   *
   * @param string $paymentMethodCode
   * @param array $paymentData
   * @return array
   * @throws Exception
   */
  public function processPayment(string $paymentMethodCode, array $paymentData)
  {
    // Get the payment method
    $paymentMethod = PaymentMethod::where('code', $paymentMethodCode)
      ->where('is_active', true)
      ->first();

    if (!$paymentMethod) {
      throw new Exception("Payment method not found or not active");
    }

    try {
      switch ($paymentMethodCode) {
        case 'opay':
          $opayService = new OpayService();
          return $opayService->createExpressCheckout($paymentData);

        case 'paystack':
          $paystackService = new PaystackService();
          return $paystackService->initializeTransaction($paymentData);

        default:
          throw new Exception("Unsupported payment method");
      }
    } catch (Exception $e) {
      Log::error("Payment processing error", [
        'payment_method' => $paymentMethodCode,
        'error' => $e->getMessage()
      ]);
      throw $e;
    }
  }

  /**
   * Verify payment using the specified payment method
   *
   * @param string $paymentMethodCode
   * @param string $reference
   * @return array
   * @throws Exception
   */
  public function verifyPayment(string $paymentMethodCode, string $reference)
  {
    // Get the payment method
    $paymentMethod = PaymentMethod::where('code', $paymentMethodCode)
      ->where('is_active', true)
      ->first();

    if (!$paymentMethod) {
      throw new Exception("Payment method not found or not active");
    }

    try {
      switch ($paymentMethodCode) {
        case 'opay':
          $opayService = new OpayService();
          return $opayService->verifyPayment($reference);

        case 'paystack':
          $paystackService = new PaystackService();
          return $paystackService->verifyTransaction($reference);

        default:
          throw new Exception("Unsupported payment method");
      }
    } catch (Exception $e) {
      Log::error("Payment verification error", [
        'payment_method' => $paymentMethodCode,
        'reference' => $reference,
        'error' => $e->getMessage()
      ]);
      throw $e;
    }
  }
}
