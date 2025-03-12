<?php

namespace App\Traits;

use App\Models\Favourite;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyFavourite
{
    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }
}
