<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = $this->roomCategory()->orderBy('price1')->first()?->price1;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'reviews' => $this->ratings()->exists()  ? $this->ratings?->count() : 'no reviews yet',
            'walk_in_price' => $price,
            'online_price' => calculateRemainingAmount($price, $this->online_price),
            'address' => $this->address . ", " . $this->city->name,
            'rating' => $this->ratings()->exists() ? round($this->ratings()->avg('number'), 1) : 'no rating yet',
            'image' => $this->background_image ? env('APP_URL') . "/storage" . $this->background_image : env('APP_URL') . '/img/9jahotel_logo.png'
        ];
    }
}
