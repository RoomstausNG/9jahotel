<?php

namespace App\Models;

use Nnjeim\World\Models\City;
use Nnjeim\World\Models\State;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CityCopy extends City
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'cities';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class, 'city_id');
    }

    public function ratings(): HasManyThrough
    {
        return $this->hasManyThrough(Rating::class, Hotel::class, 'city_id', 'hotel_id');
    }

    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(Booking::class, Hotel::class, 'city_id', 'hotel_id');
    }
}
