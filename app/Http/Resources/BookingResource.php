<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Room; // Import Room model if not already imported

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bookedItem = null;
        $firstBooking = $this->bookings->first(); // Get the first booking to check type

        if ($firstBooking) {
            if ($firstBooking->type === 'room') {
                // If it's a room booking, collect all room numbers
                $roomNumbers = $this->bookings->map(function ($booking) {
                    return $booking->bookable?->number;
                })->filter()->all(); // Map to numbers, filter out nulls, convert to array

                // Get the room type name from the first booking's bookable
                $roomTypeName = $firstBooking->bookable?->type->name ?? 'Room';

                // Format the output: Type Name followed by comma-separated numbers
                $bookedItem = strtoupper($roomTypeName . " " . implode(', ', $roomNumbers));
            } else {
                // If it's not a room (assuming hall), display the hall name
                $bookedItem = strtoupper($firstBooking->bookable?->name);
            }
        }


        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'booking_type' => $this->type,
            'Booked Item' => $bookedItem, // Use the calculated booked item string
            'amount_paid' => $this->amount,
            'commission_amount' => $this->commission_amount,
            'hotel_payable' => $this->hotel_payable,
            'amount_paid' => $this->amount_paid,
            'checked_in' => $this->checked_in?->format('d M, Y'),
            'checked_out' => $this->checked_out?->format('d M, Y'),
            'status' => $this->status,
            // 'mode_of_payment' => $this->mode_of_payment,
            'customer' => [
                'name' => ucfirst($this->customer->firstname . " " . $this->customer->lastname),
                'phone' => $this->customer->phone,
                'email' => $this->customer->email,
            ],

        ];
    }
}
