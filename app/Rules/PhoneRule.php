<?php

namespace App\Rules;

use App\Models\Customer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneRule implements ValidationRule
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
        // $exist = Customer::where('phone', $value)->first();

        $exist = Customer::where('phone', $value)->whereRelation('hotels', 'code', myHotel()->code)->first();

        $associate = myHotel()->customers()->where('phone', $value)->first();

        // dd($associate);

        // if (!$exist || !$associate) {
        //     $fail('No customer with the phone number');
        // }
        if (!$exist) {
            $fail('No customer with the phone number');
        }
    }
}