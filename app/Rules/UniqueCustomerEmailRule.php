<?php

namespace App\Rules;

use App\Models\Customer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueCustomerEmailRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exist = Customer::where('email', $value)->whereRelation('hotels', 'hotel_id', auth()->user()->id)->first();

        if ($exist) {
            $fail('Customer already exist');
        }
    }
}