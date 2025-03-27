<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GroupBooking extends Model
{
    use HasFactory, SoftDeletes, BelongToHotel;
    protected $guarded = [];


    protected $casts = [
        // JSON columns
        'meal_options' => 'array',
        'hotels' => 'array',

        // Date columns
        'room_from' => 'date',
        'room_to' => 'date',
        'hall_from' => 'date',
        'hall_to' => 'date',
        'meal_from' => 'date',
        'meal_to' => 'date',

        // Boolean columns (for completeness)
        'need_hall' => 'boolean',
        'need_meal' => 'boolean',
        'allow_external_bids' => 'boolean',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class);
    }

    public function selectedHotels()
    {
        return $this->belongsToMany(Hotel::class, 'group_booking_hotel');
    }
}