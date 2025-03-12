<?php

namespace App\Rules;

use App\Models\Room;
use App\Models\Scopes\HotelScope;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AvailableRule implements ValidationRule
{

    public function __construct(public int $roomID)
    {
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $room = Room::withoutGlobalScope(HotelScope::class)->find($this->roomID);
        if ($room->statuses()->where('date', $value)->orderByDesc('id')->first()) {
            $fail('Room not available on this date');
        }
    }
}
