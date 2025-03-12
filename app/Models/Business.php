<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Models\Scopes\HotelScope;
use App\Traits\IsCreatingHotel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

#[ScopedBy([HotelScope::class])]
class Business extends Model
{
    use HasFactory, BelongToHotel, IsCreatingHotel, Notifiable;
    protected $guarded = ['state_id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'business_id');
    }

    function payments(): HasMany
    {
        return $this->hasMany(CreditPayment::class);
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Customer::class);
    }
}
