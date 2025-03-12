<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'Booked Item' => $this->type == 'room' ? strtoupper($this->bookable->type->name . " " . $this->bookable->number) : strtoupper($this->bookable->name),
            'price' => $this->bookable->price,
            'due' => $this->due,
            'amount_paid' => $this->amount_paid,
            'discount' => $this->discount,
            'checked_in' => $this->checked_in->format('d M, Y'),
            'checked_out' => $this->checked_out->format('d M, Y'),
            'status' => $this->status,
            'mode_of_payment' => $this->mode_of_payment,
            'customer' => ucfirst($this->customer->firstname . " " . $this->customer->lastname),
            'phone' => $this->customer->phone,
            'email' => $this->customer->email,
            'image' => $this->type == 'room' ? $this->bookable->type->getFirstMediaUrl('rooms') : $this->bookable->getFirstMediaUrl('halls'),
            'agent' => $this->agent_id ? ucfirst($this->agent->firstname . " " . $this->agent->lastname) : 'Booked by customer',
            'sales' => SaleResource::collection($this->orders)
        ];
    }
}