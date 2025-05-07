<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Models\Scopes\HotelScope;
use App\Notifications\GuestBookingNotification;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\HotelBookingNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// #[ScopedBy([HotelScope::class])]
class Booking extends Model
{
    use HasFactory, BelongToHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'checked_in' => 'datetime',
        'checked_out' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];


    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    function invoice(): BelongsTo
    {
        return $this->belongsTo(BookingInvoice::class, 'booking_invoice_id');
    }

    function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }


    static public function amountGenerated($type, $start, $end): int|float
    {
        return self::where('bookable_type', $type)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->sum('due');
    }

    static public function reservation($bookable_id, $type, $start, $end)
    {
        return self::where('bookable_type', $type)
            ->where('bookable_id', $bookable_id)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);
    }

    static public function allReservation($type, $start, $end)
    {
        return self::where('bookable_type', $type)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);
    }

    public function getDateRange()
    {
        $checked_in = $this->checked_in;
        $checked_out = $this->checked_out;
        $dates = [];

        // Convert start and end dates to DateTime objects
        $startDate = new \DateTime($checked_in);
        $endDate = new \DateTime($checked_out);

        // Iterate through the date range and add each date to the array
        while ($startDate <= $endDate) {
            $dates[] = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');
        }

        return $dates;
    }


    public static function getRoomBookingByDate($date, $bookable_id)
    {
        return self::where('bookable_type', 'App\Models\Room')
            ->where('bookable_id', $bookable_id)
            ->whereDate('checked_in', '>=', $date)
            ->whereDate('checked_out', '<=', $date)->first();
    }

    public static function getHallBookingByDate($date, $bookable_id)
    {
        return self::where('bookable_type', 'App\Models\Hall')
            ->where('bookable_id', $bookable_id)
            ->whereDate('checked_in', '>=', $date)
            ->whereDate('checked_out', '<=', $date)->first();
    }

    function getAllBookingsDateRange($bookable)
    {
        $allDates = [];
        foreach ($bookable->bookings as $bookings) {
            $date = $this->getDateRange();
            array_merge($allDates, $date);
        }
        return $allDates;
    }

    function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    public function calculateDue()
    {
        $normal = $this->bookable->type->price1 * calculateDateDifferenceInDays($this->checked_in, $this->checked_out);
        $discounted = $normal - ($normal * $this->discount / 100);
        $this->update(['due' => $this->discount > 0 ? $discounted : $normal]);
    }

    static function boot()
    {
        parent::boot();

        self::creating(function ($booking) {
            // if (auth()->user()) {
            $booking->hotel_id = auth()->user() ? auth()->user()->hotel_id : $booking->hotel_id;
            // }
            $booking->code = strtoupper(IdGenerator::generate(['table' => 'bookings', 'field' => 'code', 'length' => 12, 'reset_on_prefix_change' => true, 'prefix' => '9JA' . date('y') . date('d')]));
        });

        // get the dues to pay based on price of room and days.
        self::created(function ($booking) {
            // sending notifications to hotels
            $delay = now();
            // $booking->hotel->notify((new HotelBookingNotification($booking))->delay($delay));
            // $booking->customer->notify((new HotelBookingNotification($booking))->delay($delay));


            if ($booking->bookable_type == 'App\Models\Room') {
                for ($i = 0; $i < calculateDateDifferenceInDays($booking->checked_in, $booking->checked_out); $i++) {
                    $booking->statuses()->create(['room_id' => $booking->bookable_id, 'status' => $booking->status, 'date' => $booking->checked_in->addDays($i)]);
                }
            } else {
                for ($i = 0; $i < calculateDateDifferenceInDays($booking->checked_in, $booking->checked_out); $i++) {
                    $booking->statuses()->create(['hall_id' => $booking->bookable_id, 'status' => $booking->status, 'date' => $booking->checked_in->addDays($i)]);
                }
            }

            if ($booking->customer->business_id) {
                // $booking->customer->company()->update(['balance' => ($booking->due - $booking->amount_paid)]);
                $booking->customer->company()->increment('balance', ($booking->due - $booking->amount_paid));
            }

            // notifications
            Notification::send($booking->customer, (new GuestBookingNotification($booking, $booking->bookable, $booking->status))->delay($delay->addSeconds(3)));
            Notification::send($booking->hotel, (new HotelBookingNotification($booking))->delay($delay->addSeconds(5)));
        });

        self::updated(function ($booking) {
            // $booking->bookable()->update(['status' => $booking->status]);

            if ($booking->bookable_type == 'App\Models\Room') {
                $booking->statuses()->update(['status' => $booking->status]);
            } else {
                $booking->statuses()->update(['status' => $booking->status]);
            }

            // update the company balnce to pay
            if ($booking->customer->business_id) {
                // $booking->customer->company()->update(['balance' => ($booking->due - $booking->amount_paid)]);
                $booking->customer->company()->increment('balance', ($booking->due - $booking->amount_paid));
            } else {
                $booking->orders()->update(['mode_of_payment' => $booking->mode_of_payment]);
            }
        });
    }
}
