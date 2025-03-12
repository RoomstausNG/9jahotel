<?php

namespace App\Traits;

use App\Models\Finance;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasManyFinance
{
    public function finances(): MorphMany
    {
        return $this->morphMany(Finance::class, 'financeable');
    }
}
