<?php

namespace App\Traits;

trait BookingStatus
{
    public function status($date): string
    {
        $roomSt = $this->statuses()->where('date', $date)->orderByDesc('id')->first();

        // return $roomSt ? $roomSt->status : 'available';
        if (in_array($this->status, ['dirty', 'repair', 'occupied'])) {
            return $this->status;
        } elseif ($this->bookings()->exists() && $this->statuses()->where('date', $date)->orderByDesc('id')->first()) {
            return $roomSt->status;
        } else {
            return $roomSt?->status ?? 'available';
            // return 'available';
        }
    }
}
