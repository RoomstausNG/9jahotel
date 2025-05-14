<?php

namespace App\Traits;

use App\Models\Booking;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasManyBookings
{
    public function bookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'bookable')->orderBy('created_at', 'desc');
    }

    public function getBookingDates()
    {
        $allBookingDates = [];

        // Loop through each booking of the room
        foreach ($this->bookings as $booking) {
            // Generate dates between start and end dates
            $dates = [];
            $startDate = new \DateTime($booking->checked_in);
            $endDate = new \DateTime($booking->checked_out);

            while ($startDate <= $endDate) {
                $dates[] = $startDate?->format('Y-m-d');
                $startDate->modify('+1 day');
            }

            // Merge dates with allBookingDates array
            $allBookingDates = array_merge($allBookingDates, $dates);
        }

        // Sort the dates
        sort($allBookingDates);

        return $allBookingDates;
    }
}
