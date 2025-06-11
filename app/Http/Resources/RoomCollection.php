<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoomCollection extends ResourceCollection
{

    public function __construct($resource, public $extraData)
    {
        parent::__construct($resource);
        $this->extraData = $extraData;
    }
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return [
        //     'id' => $this->id,
        //     'number' => $this->number,
        //     'price' => $this->type?->price1 ?? $this->price,
        //     'type' => $this->type?->name,
        //     'status' => roomStatus($this->resource, $this->extraData),
        //     'character' => $this->character,
        //     'floor' => $this->floor->name . " floor",
        //     'bed_type' => $this->bed_type,
        //     'image' => $this->type->getFirstMediaUrl('rooms'),
        // ];

        return [
            'rooms' => $this->collection->transform(function ($item) {
                if (is_array($item)) {
                    $item = (object) $item;
                }
                return [
                    'id' => $item->id,
                    'number' => $item->number,
                    'price' => $item->type,
                    'type' => $item->type,
                    'status' => roomStatus($item, $this->extraData),
                    'character' => $item->character,
                    'floor' => $item->floor->name . " floor",
                    'bed_type' => $item->bed_type,
                    'image' => getCloudinaryUrl($item->image),
                    'date' => $this->extraData
                ];
            })
        ];
    }
}
