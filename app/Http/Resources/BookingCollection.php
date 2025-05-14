<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BookingCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'bookings' => $this->collection->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'amount_paid' => $item->amount_paid,
                    'due' => $item->due,
                    'checked_in' => $item->checked_in?->format('d M, Y'),
                    'checked_out' => $item->checked_out?->format('d M, Y'),
                    // 'Room Info' => $item->type == 'room' ? strtoupper($item->bookable->number . ' ' . $item->bookable->type->name . ' (' . $item->bookable->price . ')') : strtoupper($item->bookable->name . ' (' . $item->bookable->price . ')'),
                    // 'discount' => $item->discount,
                    'status' => $item->status,
                    'customer' => ucfirst($item->customer->firstname . " " . $item->customer->lastname),
                    'phone' => $item->customer->phone,
                    'image' => $item->bookable->type?->getFirstMediaUrl('rooms'),
                    'agent' => $item->agent_id ? ucfirst($item->agent->firstname . " " . $item->agent->lastname) : 'Booked by customer',
                ];
            }),
        ];
    }
}
