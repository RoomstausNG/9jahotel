<?php

namespace App\Http\Resources;

use App\Models\Room;
use App\Models\Scopes\HotelScope;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomCategoryResource extends JsonResource
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
            'price' => $this->price1,
            'promo_price' => $this->promo_price,
            'promo_start_day' => $this->promo_start_day?->format('d M, Y'),
            'promo_end_day' => $this->promo_end_day?->format('d M, Y'),
            'weekend_price' => $this->weekend_price,
            'weekend_start_day' => $this->weekend_start_day,
            'weekend_end_day' => $this->weekend_end_day,
            'image' => $this->getFirstMediaUrl('rooms'),
            'images' => $this->media->map(function ($item) {
                return $item->getFullUrl();
            }),
            'rooms' => RoomsResource::collection(Room::withoutGlobalScope(HotelScope::class)->with(['type'])->where('type_id', $this->id)->get()),
            'hotel_id' => $this->hotel_id,
        ];
    }
}
