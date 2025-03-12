<?php

namespace App\Http\Resources;

use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomCategoriesResource extends JsonResource
{

    public $collects = Type::class;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'walk_in_price' => $this->price1,
            'promo_price' => calculateRemainingAmount($this->price1, $this->hotel->online_price),
            'promo_start_day' => $this->promo_start_day?->format('d M, Y'),
            'promo_end_day' => $this->promo_end_day?->format('d M, Y'),
            'weekend_price' => $this->weekend_price,
            'available_rooms' => $this->rooms->filter(fn($room) => $room->isAvailable(request()->checkin, request()->checkout))
                ->count(),
            'weekend_start_day' => $this->weekend_start_day,
            'weekend_end_day' => $this->weekend_end_day,
            // 'image' => $this->getFirstMediaUrl('rooms'),
            // 'images' => $this->getMedia('rooms'),
            // 'images' => $this->media->map(function ($item) {
            //     return $item->getFullUrl();
            // }),
        ];
    }
}