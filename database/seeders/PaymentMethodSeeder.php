<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'OPay',
                'code' => 'opay',
                'description' => 'Pay with OPay',
                'is_active' => true,
                'config' => json_encode([
                    'base_url' => config('opay.base_url'),
                    'secret_key' => config('opay.secret_key'),
                    'public_key' => config('opay.public_key'),
                    'merchant_id' => config('opay.merchant_id'),
                ])
            ],
            [
                'name' => 'Paystack',
                'code' => 'paystack',
                'description' => 'Pay with Paystack',
                'is_active' => true,
                'config' => json_encode([
                    'public_key' => config('paystack.publicKey'),
                    'secret_key' => config('paystack.secretKey'),
                    'payment_url' => config('paystack.paymentUrl'),
                ])
            ]
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
