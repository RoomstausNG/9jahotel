<?php

namespace App\Http\Resources;

use id;
use App\Models\Hall;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hall = Hall::find($this->id);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'status' => roomStatus($hall, $this->extraData),
            'capacity' => $this->capacity,
            'description' => $this->description,
        ];
    }
}