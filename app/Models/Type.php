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

        self::updated(function ($type) {
            $type->rooms->each(fn($room) => $room->update(['price' => $type->price1]));
        });
    }
}