<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomsResource extends JsonResource
{

    protected $additionalData;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'walk_in_price' => $this->getPrice(),
            'online_price' => calculateRemainingAmount($this->type->price1, $this->hotel->online_price),
            'type' => $this->type?->name,
            'is_available' => $this->isAvailable(request()->checkin, request()->checkout),
            'character' => $this->character,
            'floor' => $this->floor->name . " floor",
            'bed_type' => $this->bed_type,
            // 'image' => $this->type->getFirstMediaUrl('rooms'),
        ];
    }
}