<?php

namespace App\Traits;

use App\Models\Scopes\HotelScope;

trait WithoutScope
{
    public function withoutScope()
    {
        return $this->withoutGlobalScope(HotelScope::class);
    }
}
