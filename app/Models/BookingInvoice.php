<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Jobs\CreateTransactionJob;
use Illuminate\Support\Facades\Bus;
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

    public function scopeMy($query)
    {
        return $query->whereHas('customer', function ($q) {
            $q->where('id', auth('customers')->id());
        });
    }


    /**
     * Send payment confirmation emails to customer and hotel
     */
    public function sendPaymentEmails()
    {
        // Customer email
        if ($this->customer && $this->customer->email) {
            // Send payment confirmation to customer
            Mail::to($this->customer->email)
                ->queue(new \App\Mail\PaymentConfirmationCustomerMail($this));

            // For non-PayAtHotel bookings, also send the booking confirmation email
            // since it wasn't sent at creation time
            if (strtolower($this->type) !== 'payathotel') {
                $mailClass = null;

                switch (strtolower($this->type)) {
                    case 'paynow':
                        $mailClass = \App\Mail\BookingPayNowMail::class;
                        break;
                    case 'nonrefundable':
                        $mailClass = \App\Mail\BookingNonRefundableMail::class;
                        break;
                    default:
                        $mailClass = \App\Mail\BookingPayNowMail::class;
                }

                if ($mailClass) {
                    Mail::to($this->customer->email)
                        ->queue(new $mailClass($this));
                }
            }
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

            // Only send PayAtHotel emails immediately
            // Other types will be sent after payment confirmation
            if (strtolower($type) === 'payathotel' && $invoice->customer && $invoice->customer->email) {
                Mail::to($invoice->customer->email)
                    ->queue(new \App\Mail\BookingPayAtHotelMail($invoice));
            }

            // Always create the transaction
            Bus::dispatch(new CreateTransactionJob($invoice));
        });
        self::updated(function ($invoice) {
            if ($invoice->transaction && $invoice->transaction->status == "cancelled") {
                $invoice->transaction()->update([
                    'status' => $invoice->status
                ]);
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
        // Ensure relations are loaded
        if (!$this->relationLoaded('hotel') && $this->hotel_id) {
            $this->load('hotel');
        }

        // We'll try to compute the original (pre-coupon) amount from associated bookings.
        // Original amount = sum(booking_base_price * days - online_discount_for_booking)
        $originalAmount = 0.0;
        if (!$this->relationLoaded('bookings')) {
            $this->load('bookings.bookable');
        }

        foreach ($this->bookings as $booking) {
            $bookable = $booking->bookable;
            if (!$bookable) {
                continue;
            }

            // Determine price field: rooms use price1, halls may use price
            $price = $bookable->price1 ?? $bookable->price ?? 0;
            $days = calculateDateDifferenceInDays($booking->checked_in, $booking->checked_out);
            $normal = $price * $days;
            $onlineDiscountPercent = $booking->discount ?? 0; // percent stored on booking
            $onlineDiscountAmount = ($normal * ($onlineDiscountPercent / 100));
            $originalBookingAmount = $normal - $onlineDiscountAmount;
            $originalAmount += $originalBookingAmount;
        }

        // If we could not derive an original amount (no bookings), fallback to previous logic using invoice amount
        if ($originalAmount <= 0) {
            // Fallback: use invoice amount as original (best-effort)
            $originalAmount = $this->amount;
        }

        // Get commission percentage
        $percentage = $this->hotel->percentage_charge ?? 10;

        // Original commission based on pre-coupon amount
        $originalCommission = ($percentage / 100) * $originalAmount;

        // Compute coupon discount against original amount if coupon set
        $couponDiscount = 0.0;
        if ($this->coupon_id) {
            try {
                $coupon = \App\Models\Coupon::find($this->coupon_id);
                if ($coupon) {
                    $couponDiscount = $coupon->calculateDiscount((float)$originalAmount);
                }
            } catch (\Exception $e) {
                $couponDiscount = 0.0;
            }
        }

        // Commission after absorbing coupon discount (never negative)
        $commissionAfterCoupon = max(0, $originalCommission - $couponDiscount);

        // Hotel payable remains based on original pre-coupon calculation
        $hotelPayable = $originalAmount - $originalCommission;

        // Persist rounded values
        $this->update([
            'commission_amount' => round($commissionAfterCoupon, 2),
            'hotel_payable' => round($hotelPayable, 2),
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
