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
            $invoice->reference = strtoupper(IdGenerator::generate([
                'table' => 'booking_invoices',
                'field' => 'reference',
                'length' => 12,
                'reset_on_prefix_change' => true,
                'prefix' => 'RE9A' . date('d')
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