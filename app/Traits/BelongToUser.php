<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongToUser
{
    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}