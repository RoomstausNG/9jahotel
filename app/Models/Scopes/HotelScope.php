<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class HotelScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Get the hotel_id of the authenticated user
        $hotelId = auth()->user()?->hotel_id;

        // Apply the constraint to the query
        $builder->where('hotel_id', $hotelId);
        // if (auth()->check()) {
        //     $builder->where('hotel_id', $hotelId);
        // }
    }
}