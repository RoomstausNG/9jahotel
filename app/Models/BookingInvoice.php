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

            // Send booking invoice email to customer (queued)
            Mail::to($invoice->customer->email)->queue(new \App\Mail\BookingInvoiceEmail($invoice));
            // Send booking invoice email to hotel (queued)
            Mail::to($invoice->hotel->email)->queue(new \App\Mail\BookingInvoiceEmail($invoice));
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
