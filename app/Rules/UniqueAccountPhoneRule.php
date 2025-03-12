<?php

namespace App\Rules;

use App\Models\Hotel;
use Closure;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueAccountPhoneRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userPhone = User::where('phone', $value)->first();
        $hotelPhone = Hotel::where('telephone', $value)->first();

        if ($userPhone || $hotelPhone) {
            $fail('The phone number already exist, kindly use another phone');
        }
    }
}