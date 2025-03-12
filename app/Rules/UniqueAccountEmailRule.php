<?php

namespace App\Rules;

use App\Models\Hotel;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueAccountEmailRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userEmail = User::where('email', $value)->first();
        $hotelEmail = Hotel::where('email', $value)->first();

        if ($userEmail || $hotelEmail) {
            $fail('There is an hotel with the email address, kindly use another email');
        }
    }
}