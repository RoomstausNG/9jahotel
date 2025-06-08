<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavouriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = $this->hotel->roomCategory()->orderBy('price1')->first()?->price1;
        return [
            'id' => $this->hotel->id,
            'name' => $this->hotel->name,
            'code' => $this->hotel->code,
            'reviews' => $this->hotel->ratings()->exists()  ? $this->hotel->ratings?->count() : 'no reviews yet',
            'walk_in_price' => $price,
            'online_price' => calculateRemainingAmount($price, $this->hotel->online_price),
            'address' => $this->hotel->address . ", " . $this->hotel->city->name,
            'rating' => $this->hotel->ratings()->exists() ? round($this->hotel->ratings()->avg('number'), 1) : 'no rating yet',
            'image' => $this->hotel->background_image ? env('APP_URL') . "/storage" . $this->hotel->background_image : env('APP_URL') . '/img/9jahotel_logo.png'
        ];
    }
}
