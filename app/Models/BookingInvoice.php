<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
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

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'booking_invoice_id');
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
            // Call the transaction API to create a transaction for this invoice
            try {
                $response = Http::post(url('/api/transactions'), [
                    'booking_reference' => $invoice->reference,
                    'amount' => $invoice->amount,
                    'payment_method' => $invoice->payment_method ?? 'wallet',
                ]);
            } catch (\Exception $e) {
                // Log or handle error
            }
        });
        self::updated(function ($invoice) {
            if ($invoice->transaction) {
                try {
                    Http::patch(url('/api/transactions/' . $invoice->transaction->reference . '/status'), [
                        'status' => $invoice->status
                    ]);
                } catch (\Exception $e) {
                    // Log or handle error
                }
            }
        });
    }

    /**
     * Calculate and save commission amount and hotel payable based on booking amount
     *
     * @return void
     */
    public function calculateAndSaveCommission()
    {
        // Load hotel relationship if not already loaded
        if (!$this->relationLoaded('hotel') && $this->hotel_id) {
            $this->load('hotel');
        }

        // Skip calculation if no hotel or amount
        if (!$this->hotel || !$this->amount) {
            return;
        }

        // Get the commission percentage from hotel (default to 10% if not set)
        $percentage = $this->hotel->percentage_charge ?? 10;

        // Calculate commission amount
        $commissionAmount = ($percentage / 100) * $this->amount;

        // Calculate hotel payable (total amount minus commission)
        $hotelPayable = $this->amount - $commissionAmount;

        // Update the fields
        $this->update([
            'commission_amount' => round($commissionAmount),
            'hotel_payable' => round($hotelPayable)
        ]);
    }

    /**
     * Get the commission percentage from the hotel
     *
     * @return float
     */
    public function getCommissionPercentage()
    {
        return $this->hotel?->percentage_charge ?? 10;
    }

    /**
     * Calculate commission amount without saving
     *
     * @return float
     */
    public function calculateCommissionAmount()
    {
        if (!$this->amount) {
            return 0;
        }

        $percentage = $this->getCommissionPercentage();
        return ($percentage / 100) * $this->amount;
    }

    /**
     * Calculate hotel payable amount without saving
     *
     * @return float
     */
    public function calculateHotelPayable()
    {
        if (!$this->amount) {
            return 0;
        }

        $commissionAmount = $this->calculateCommissionAmount();
        return $this->amount - $commissionAmount;
    }
}