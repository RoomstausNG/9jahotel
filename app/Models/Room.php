<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Traits\HasManyBookings;
use App\Models\Scopes\HotelScope;
use App\Traits\HasManyImage;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// #[ScopedBy(HotelScope::class)]
class Room extends Model implements HasMedia
{
    use HasFactory, BelongToHotel, HasManyBookings, HasManyImage, InteractsWithMedia;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }


    public function currentStatus($date): string
    {
        return roomStatus($this, $date);
    }


    public function isPromoPrice()
    {
        $currentDate = now()->toDateString();

        return $currentDate >= $this->type?->promo_start_day && $currentDate <= $this->type?->promo_end_day && ($this->type?->promo_price > 0);
    }

    public function isWeekendPrice()
    {
        $currentDate = now()->toDateString();

        return ($this->type->weekend_price || $this->type->weekend_price > 0) && $this->isWeekend($currentDate, ucfirst($this->type->weekend_start_day), ucfirst($this->type->weekend_end_day));
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
            return $this->type->promo_price;
        } elseif ($this->isWeekendPrice()) {
            return $this->type->weekend_price;
        } else {
            return $this->price;
        }
    }

    /**
     * Checks if a room is available within a given date range.
     *
     * @param string $start The start date of the range.
     * @param string $end The end date of the range.
     * @return bool True if the room is available, false otherwise.
     */
    function isAvailable($start, $end): bool
    {
        foreach (getDatesFromRange($start, $end) as $date) {
            if ($this->statuses()->whereDate('date', $date)->exists()) {
                return false;
            }
        }
        return true;
    }


    static function amountGenerated($start, $end)
    {
        self::has('bookings')
            ->withSum('bookings as total', 'due')
            ->whereHas(
                'bookings',
                fn(Builder $query) => $query->whereBetween('created_at', [$start, $end])
            )
            ->get();
    }

    function lastBooking()
    {
        return $this->bookings()->orderByDesc('id')->first();
    }


    public function getBookingByDate($date)
    {
        return $this->bookings()->whereDate('checked_in', '<=', $date)
            ->whereDate('checked_out', '>=', $date)
            ->first();
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    public function dateStatus($date)
    {
        return $this->statuses()->whereDate('date', $date)->first();
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($room) {
            $type = Type::find($room->type_id);
            $room->hotel_id = $type->hotel_id;
            $type = Type::find($room->type_id);
            $room->price = $type->price1;
        });

        self::created(function ($room) {
            $room->statuses()->update(['status' => $room->status]);
        });

        self::updated(function ($room) {
            $room->statuses()->update(['status' => $room->status]);
            $type = Type::find($room->type_id);
            $room->hotel_id = $type->hotel_id;
        });

        self::deleted(function ($room) {
            // deleting booking when room got deleted
            $room->bookings->each(fn($booking) => $booking->delete());
        });
    }
}