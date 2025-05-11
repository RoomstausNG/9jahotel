<?php

namespace App\Models;

use Nnjeim\World\Models\City;
use Nnjeim\World\Models\State;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class, 'city_id');
    }

    function ratings(): HasManyThrough
    {
        return $this->hasManyThrough(Rating::class, Hotel::class, 'city_id', 'hotel_id');
    }

    function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(Booking::class, Hotel::class, 'city_id', 'hotel_id');
    }
}