<?php

namespace App\Models;

use App\Models\Scopes\HotelScope;
use App\Traits\BelongToHotel;
use App\Traits\HasManyImage;
use App\Traits\IsCreatingHotel;
use App\Traits\WithoutScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// #[ScopedBy(HotelScope::class)]
class Type extends Model implements HasMedia
{
    use HasFactory, BelongToHotel, HasManyImage, WithoutScope, InteractsWithMedia;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'promo_start_day' => 'date',
        'promo_end_day' => 'date',
    ];

    public function registerMediaCollections(): void
    {
        // ok done
        $this->addMediaCollection('rooms')
            ->useFallbackUrl(asset('img/logo.png'))
            ->useFallbackPath(public_path('/img/logo.png'));
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function halls(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function isPromoPrice()
    {
        $currentDate = now()->toDateString();

        return $currentDate >= $this->promo_start_day && $currentDate <= $this->promo_end_day && ($this->promo_price > 0);
    }

    public function isWeekendPrice()
    {
        $currentDate = now()->toDateString();

        return ($this->weekend_price || $this->weekend_price > 0) && $this->isWeekend($currentDate, ucfirst($this->weekend_start_day), ucfirst($this->weekend_end_day));
    }

    private function isWeekend($date = null, $startDay, $endDay)
    {
        // If $date is not provided, default to today's date
        if (!$date) {
            $date = now()->toDateString();
        }

        // Get the current day of the week
        $currentDay = date('l', strtotime($date));

        // Get the day of the week index for the start and end days
        $startDayIndex = array_search($startDay, ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
        $endDayIndex = array_search($endDay, ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
        $currentDayIndex = array_search($currentDay, ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);

        // Check if the current day falls within the range of the weekend
        if ($startDayIndex <= $endDayIndex) {
            return $currentDayIndex >= $startDayIndex && $currentDayIndex <= $endDayIndex;
        } else {
            return $currentDayIndex >= $startDayIndex || $currentDayIndex <= $endDayIndex;
        }
    }

    public function getPrice()
    {
        if ($this->isPromoPrice()) {
            return $this->promo_price;
        } elseif ($this->isWeekendPrice()) {
            return $this->weekend_price;
        } else {
            return $this->price;
        }
    }

    public static function boot()
    {
        // if (auth()->user()) {
        //     static::addGlobalScope(new HotelScope);
        // }

        parent::boot();

        // Update room prices when type is updated
        self::updated(function ($type) {
            $type->rooms->each(fn($room) => $room->update(['price' => $type->price1]));

            // Update hotel's lowest room price
            self::updateHotelLowestPrice($type->hotel_id);
        });

        // Update hotel's lowest room price when type is created
        self::created(function ($type) {
            self::updateHotelLowestPrice($type->hotel_id);
        });

        // Update hotel's lowest room price when type is deleted
        self::deleted(function ($type) {
            self::updateHotelLowestPrice($type->hotel_id);
        });
    }

    /**
     * Update the hotel's room_lowest_price field with the lowest price among all types
     */
    protected static function updateHotelLowestPrice($hotelId)
    {
        $hotel = Hotel::find($hotelId);

        if ($hotel) {
            // Get all types for this hotel
            $types = self::where('hotel_id', $hotelId)->get();

            if ($types->count() > 0) {
                // Find the lowest price among all types
                $lowestPrice = $types->min(fn($type) => $type->price1);

                // Update the hotel's lowest price
                $hotel->update(['room_lowest_price' => $lowestPrice]);
            } else {
                // No types, set lowest price to 0 or null
                $hotel->update(['room_lowest_price' => 0]);
            }
        }
    }
}
