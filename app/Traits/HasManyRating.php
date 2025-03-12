<?php

namespace App\Traits;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasManyRating
{
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
