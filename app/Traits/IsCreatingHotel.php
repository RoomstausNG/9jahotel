<?php

namespace App\Traits;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait IsCreatingHotel
{
    static function booted()
    {
        parent::booted();

        self::creating(function ($model) {
            $model->hotel_id = auth()->check() ? auth()->user()->hotel_id : null;
        });
    }
}
