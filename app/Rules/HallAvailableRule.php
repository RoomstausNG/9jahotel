<?php

namespace App\Rules;

use Closure;
use App\Models\Booking;
use App\Models\Hall;
use Illuminate\Contracts\Validation\ValidationRule;

class HallAvailableRule implements ValidationRule
{

    protected $hall;
    protected $start;
    protected $end;

    public function __construct(Hall $hall, $start, $end)
    {
        $this->hall = $hall;
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
        $overlap = Booking::where('bookable_id', $this->hall->id)->where('bookable_type', 'App\Models\Hall')
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