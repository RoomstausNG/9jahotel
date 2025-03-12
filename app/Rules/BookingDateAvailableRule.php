<?php

namespace App\Rules;

use Closure;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Contracts\Validation\ValidationRule;

class BookingDateAvailableRule implements ValidationRule
{

    protected $room;
    protected $start;
    protected $end;

    public function __construct(Room $room, $start, $end)
    {
        $this->room = $room;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $overlap = Booking::where('bookable_id', $this->room->id)->where('bookable_type', 'App\Models\Room')
            ->where(function ($query) {
                $query->whereBetween('checked_in', [$this->start, $this->end])
                    ->orWhereBetween('checked_out', [$this->start, $this->end])
                    ->orWhere(function ($query) {
                        $query->where('checked_in', '<', $this->start)
                            ->where('checked_out', '>', $this->end);
                    });
            })->exists();

        if ($overlap) {
            $fail('Selected dates are unavailable! It conflicts with an existing booking.');
        }
    }
}
