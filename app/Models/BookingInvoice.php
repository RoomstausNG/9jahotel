<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
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
        'checked_in' => 'datetime',
        'checked_out' => 'datetime',
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


    /**
     * Send payment confirmation emails to customer and hotel
     */
    public function sendPaymentEmails()
    {
        // Customer email
        if ($this->customer && $this->customer->email) {
            Mail::to($this->customer->email)
                ->queue(new \App\Mail\PaymentConfirmationCustomerMail($this));
        }

        // Hotel email (fallback to hotel email, or admin if not set)
        $hotelEmail = $this->hotel->email ?? 'info@9jahotel.com';
        Mail::to($hotelEmail)
            ->queue(new \App\Mail\PaymentConfirmationHotelMail($this));
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }


    public static function booted()
    {
        parent::booted();

        self::creating(function ($invoice) {
            // Get 3 digits from hotel code (numbers only, pad if needed)
            $hotelCode = isset($invoice->hotel) && isset($invoice->hotel->code) ? preg_replace('/\D/', '', $invoice->hotel->code) : '000';
            $hotelCode = str_pad(substr($hotelCode, 0, 3), 3, '0', STR_PAD_RIGHT);

            // Get 3 random digits from current time (e.g. seconds + microseconds)
            $micro = (string) microtime(true);
            $micro = preg_replace('/\D/', '', $micro); // remove non-digits
            $randTime = substr($micro, -6, 3); // take 3 digits from the last 6 digits
            if (strlen($randTime) < 3) {
                $randTime = str_pad($randTime, 3, '0');
            }

            $prefix = '9JA' . $hotelCode . $randTime;
            $invoice->reference = strtoupper(IdGenerator::generate([
                'table' => 'booking_invoices',
                'field' => 'reference',
                'length' => 12,
                'reset_on_prefix_change' => true,
                'prefix' => $prefix
            ]));
        });

        self::created(function ($invoice) {
            // Determine email type
            $type = $invoice->type;
            $mailClass = null;
            switch (strtolower($type)) {
                case 'paynow':
                    $mailClass = \App\Mail\BookingPayNowMail::class;
                    break;
                case 'payathotel':
                    $mailClass = \App\Mail\BookingPayAtHotelMail::class;
                    break;
                case 'nonrefundable':
                    $mailClass = \App\Mail\BookingNonRefundableMail::class;
                    break;
                default:
                    $mailClass = \App\Mail\BookingPayNowMail::class;
            }
            if ($mailClass && $invoice->customer && $invoice->customer->email) {
                Mail::to($invoice->customer->email)
                    ->queue(new $mailClass($invoice));
            }
        });
    }
}