<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Traits\HasManyBookings;
use App\Models\Scopes\HotelScope;
use App\Traits\HasManyImage;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// #[ScopedBy(HotelScope::class)]
class Hall extends Model implements HasMedia
{
    use HasFactory, BelongToHotel, HasManyBookings, HasManyImage, InteractsWithMedia;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    function currentStatus($date): string
    {
        return roomStatus($this, $date);
    }

    public function getBookingByDate($date)
    {
        return $this->bookings()->whereDate('checked_in', '<=', $date)
            ->whereDate('checked_out', '>=', $date)
            ->first();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function isPromoPrice()
    {
        $currentDate = now()->toDateString();

        return $currentDate >= $this->promo_start_day && $currentDate <= $this->promo_end_day && ($this->promo_price > 0);
    }

    public function getPrice()
    {
        if ($this->isPromoPrice()) {
            return $this->promo_price;
        } else {
            return $this->price;
        }
    }


    function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    function lastBooking()
    {
        return $this->bookings()->orderByDesc('id')->first();
    }

    protected static function updateHotelLowestPrice($hotelId)
    {
        $hotel = Hotel::find($hotelId);

        if ($hotel) {
            // Get all halls for this hotel
            $halls = self::where('hotel_id', $hotelId)->get();

            if ($halls->count() > 0) {
                // Find the lowest price among all halls
                $lowestPrice = $halls->min('price');

                // Update the hotel's lowest hall price
                $hotel->update(['hall_lowest_price' => $lowestPrice]);
            } else {
                // No halls, set lowest price to 0
                $hotel->update(['hall_lowest_price' => 0]);
            }
        }
    }


    static function boot()
    {
        parent::boot();

        self::created(function ($hall) {
            // update the pricing for hall
            self::updateHotelLowestPrice($hall->hotel_id);
        });

        // self::creating(function ($hall) {
        //     $hall->hotel_id = auth()->check() ? auth()->user()->hotel_id : null;
        // });

        self::updated(function ($hall) {
            // update the pricing for hall
            self::updateHotelLowestPrice($hall->hotel_id);
        });

        self::deleted(function ($hall) {

            // update the pricing for hall
            self::updateHotelLowestPrice($hall->hotel_id);

            // deleting booking when room got deleted
            $hall->bookings->each(fn($booking) => $booking->delete());
        });
    }
}