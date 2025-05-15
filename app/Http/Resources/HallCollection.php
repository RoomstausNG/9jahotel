<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class HallCollection extends ResourceCollection
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
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'status' => roomStatus($item, $this->extraData),
                    'capacity' => $item->capacity,
                    'description' => $item->description,
                    'date' => $this->extraData
                ];
            })
        ];
    }
}