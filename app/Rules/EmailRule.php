<?php

namespace App\Rules;

use Closure;
use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailRule implements ValidationRule
{
    function __construct(public $customer)
    {
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exist = Customer::where('email', $value)->first();

        if ($exist && !$this->customer) {
            $fail('There is a customer with the provided email address');
        }
    }
}