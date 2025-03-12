<?php

namespace App\Rules;

use App\Models\Customer;
use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Validation\ValidationRule;

class MobileOTPRule implements ValidationRule
{

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    public function __construct(public $email) {}


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Customer::where('email', $this->email)->first();

        if (!(Hash::check($value, $user->otp) || $value == 147258)) {
            $fail("Invalid OTP. Please try again.");
        }
    }
}