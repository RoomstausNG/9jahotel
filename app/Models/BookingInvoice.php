<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingInvoice extends Model
{
    use HasFactory, SoftDeletes, BelongToHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    /**
     * Retrieve the bookings associated with this booking invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'booking_invoice_id');
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeMy(Builder $query)
    {
        $customer = auth('customers')->user();
        return $query->where('customer_id', $customer->id);
    }


    public static function booted()
    {
        parent::booted();

        self::creating(function ($invoice) {
            $invoice->reference = strtoupper(IdGenerator::generate(['table' => 'booking_invoices', 'field' => 'reference', 'length' => 12, 'reset_on_prefix_change' => true, 'prefix' => 'RE9A' . date('d')]));
        });

        self::created(function ($invoice) {
            $commission = round($invoice->amount * $invoice->hotel->percentage_charge / 100);
            $amountPayable = $invoice->amount - $commission;
            $invoice->commission_amount = $commission;
            $invoice->hotel_payable = $amountPayable;
            $invoice->save();

            // Eager load relationships for serialization
            $invoice->load(['customer', 'hotel', 'bookings.bookable']);


            // Normalize payment type to one of three options: 'PayAtHotel', 'PayNow', 'NonRefundable'
            $rawType = $invoice->payment_type ?? 'PayAtHotel';
            $normalizedType = match (strtolower(str_replace(['-', '_', ' '], '', $rawType))) {
                'payathotel' => 'PayAtHotel',
                'paynow', 'online' => 'PayNow',
                'nonrefundable' => 'NonRefundable',
                default => 'PayAtHotel',
            };

            $totalRooms = $invoice->bookings()->count();

            // Guest message (updated for PayAtHotel to show as unpaid/unconfirmed)
            $hotelName = strtoupper($invoice->hotel->name);
            $amount = formatAsNaira($invoice->amount);
            $bookingNumber = $invoice->reference;
            $bookedBy = $invoice->customer->firstname . ' ' . $invoice->customer->lastname;
            $checkin = optional($invoice->bookings->first())->checked_in;
            $checkout = optional($invoice->bookings->first())->checked_out;
            $roomSummary = $invoice->bookings->count() . ' ';
            if ($invoice->bookings->count() > 0) {
                $roomTypes = $invoice->bookings->map(function ($b) {
                    return strtoupper(optional($b->bookable->type)->name ?? 'ROOM');
                })->unique()->implode(', ');
                $roomSummary .= $roomTypes . ' Room(s)';
            } else {
                $roomSummary .= 'Room(s)';
            }

            switch ($normalizedType) {
                case 'PayAtHotel':
                    $guestMessage = "Your booking with {$hotelName} is unconfirmed and not guaranteed.\nPlease make payment of {$amount} to immediately confirm your booking and room reservation. Unconfirmed booking may be cancelled at any time by the hotel.\nIf you wish to make payment arrangement, please reach the hotel.\nYour booking details are below:\n\nRoom Reservation\t{$roomSummary}\nCheck-in\t{$checkin}\nCheck-out\t{$checkout}\nBooking number\t{$bookingNumber}\nBooked By\t{$bookedBy}\nThe hotel reserves the right to change your selected room to a room in same or higher category for your comfort in the rare case of your selected room being unavailable.\n9jahotel";
                    break;
                case 'PayNow':
                    $guestMessage = "Your booking with {$hotelName} is received but unconfirmed. Payment is pending. You have reserved {$roomSummary}. Once payment is received, your booking will be confirmed.\nYour booking details are below:\n\nRoom Reservation\t{$roomSummary}\nCheck-in\t{$checkin}\nCheck-out\t{$checkout}\nBooking number\t{$bookingNumber}\nBooked By\t{$bookedBy}\nThe hotel reserves the right to change your selected room to a room in same or higher category for your comfort in the rare case of your selected room being unavailable.\n9jahotel";
                    break;
                case 'NonRefundable':
                    $guestMessage = "Your non-refundable booking with {$hotelName} is received but unconfirmed. Payment is pending. You have reserved {$roomSummary}. Once payment is received, your booking will be confirmed.\nYour booking details are below:\n\nRoom Reservation\t{$roomSummary}\nCheck-in\t{$checkin}\nCheck-out\t{$checkout}\nBooking number\t{$bookingNumber}\nBooked By\t{$bookedBy}\nThe hotel reserves the right to change your selected room to a room in same or higher category for your comfort in the rare case of your selected room being unavailable.\n9jahotel";
                    break;
                default:
                    $guestMessage = "Your booking with {$hotelName} is received. You have reserved {$roomSummary}.\nYour booking details are below:\n\nRoom Reservation\t{$roomSummary}\nCheck-in\t{$checkin}\nCheck-out\t{$checkout}\nBooking number\t{$bookingNumber}\nBooked By\t{$bookedBy}\nThe hotel reserves the right to change your selected room to a room in same or higher category for your comfort in the rare case of your selected room being unavailable.\n9jahotel";
            }

            // Hotel owner message
            switch ($normalizedType) {
                case 'PayAtHotel':
                    $hotelMessage = 'A guest has made a booking and will pay at the hotel. ' . $totalRooms . ' room(s)/hall(s) reserved.';
                    break;
                case 'PayNow':
                    $hotelMessage = 'A guest has made a booking (Pay Now). Awaiting payment confirmation. ' . $totalRooms . ' room(s)/hall(s) reserved.';
                    break;
                case 'NonRefundable':
                    $hotelMessage = 'A guest has made a non-refundable booking. Awaiting payment confirmation. ' . $totalRooms . ' room(s)/hall(s) reserved.';
                    break;
                default:
                    $hotelMessage = 'A guest has made a booking. ' . $totalRooms . ' room(s)/hall(s) reserved.';
            }

            // Send booking invoice email to customer (queued)
            Mail::to($invoice->customer->email)->queue(new \App\Mail\BookingInvoiceEmail($invoice, $guestMessage));
            // Send booking invoice email to hotel (queued)
            Mail::to($invoice->hotel->email)->queue(new \App\Mail\BookingInvoiceEmail($invoice, $hotelMessage));
        });
    }

    /**
     * Call this method after payment is confirmed to send payment emails.
     */
    public function sendPaymentEmails()
    {
        $this->load(['customer', 'hotel', 'bookings.bookable']);
        Mail::to($this->customer->email)->queue(new \App\Mail\BookingPaymentEmail($this));
        Mail::to($this->hotel->email)->queue(new \App\Mail\BookingPaymentEmail($this));
    }
}
