<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Traits\HasManyFavourite;
use App\Traits\HasManyRating;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Nnjeim\World\Models\City;

// #[ScopedBy([HotelScope::class])]
class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, HasManyFavourite, HasManyRating, Notifiable, BelongToHotel;
    protected $guarded = ['bookable_id', 'discount', 'mode_of_payment', 'bookable_type',  'checked_in', 'checked_out', 'amount_paid'];

    protected $hidden = [
        'remember_token',
    ];

    public function getNameAttribute(): string
    {
        return ucfirst($this->attributes['firstname'] . ' ' . $this->attributes['lastname']);
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class)->withTimestamps();
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    function scopeMyCustomer(Builder $query)
    {
        return $query->whereRelation('hotels', 'hotel_id', auth()->user()->hotel_id);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }
}
