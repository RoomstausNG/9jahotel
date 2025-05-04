<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Nnjeim\World\Models\City;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Notification;

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
        return $this->belongsToMany(Hotel::class, 'group_booking_hotel');
    }

    public function selectedHotels()
    {
        return $this->belongsToMany(Hotel::class, 'group_booking_hotel');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    protected static function booted()
    {
        parent::booted();

        // Attach hotels to the booking after creation
        self::created(function ($booking) {
            if (is_array($booking->hotels)) {
                foreach ($booking->hotels as $hotelId) {
                    $booking->hotels()->attach($hotelId);
                }
            }
            // Send notification email to info@9jahotel.com (queued)
            Notification::route('mail', 'info.bowofade@gmail.com')
                ->notify(new \App\Notifications\GroupBookingCreatedNotification($booking));
        });
    }
}
