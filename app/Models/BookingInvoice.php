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
use App\Models\Coupon;

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
        'sub_total' => 'float',
        'amount' => 'float',
        'online_discount' => 'float',
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
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
        // Load relationships if not already loaded
        if (!$this->relationLoaded('hotel')) {
            $this->load('hotel');
        }
        if (!$this->relationLoaded('bookings.bookable')) {
            $this->load('bookings.bookable');
        }
        if ($this->coupon_id && !$this->relationLoaded('coupon')) {
            $this->load('coupon');
        }

        // Skip calculation if no hotel or bookings
        if (!$this->hotel || $this->bookings->isEmpty()) {
            return;
        }

        // Calculate the original base price from the bookings
        $days = calculateDateDifferenceInDays($this->checked_in, $this->checked_out);
        $basePrice = 0;
        foreach ($this->bookings as $booking) {
            if ($booking->bookable) {
                // Assumes 'price1' for Room and 'price' for Hall
                $priceField = $booking->bookable instanceof Room ? 'price1' : 'price';
                $basePrice += $booking->bookable->{$priceField};
            }
        }
        $basePrice *= $days;

        // Get the hotel's commission percentage
        $percentage = $this->hotel->percentage_charge ?? 10;

        // Calculate initial commission based on the price BEFORE any discounts
        $initialCommission = ($percentage / 100) * $basePrice;

        // Calculate hotel payable based on the price BEFORE any discounts.
        // This amount is fixed and not affected by coupons.
        $hotelPayable = $basePrice - $initialCommission;

        // The online discount is the difference between the base price and what would be paid without a coupon
        $onlineDiscountValue = 0;
        if (strtolower($this->type) === 'nonrefundable') {
            $onlineDiscountValue = ($basePrice * ($this->hotel->non_refundable_price ?? 0)) / 100;
        } else {
            $onlineDiscountValue = ($basePrice * ($this->hotel->online_price ?? 0)) / 100;
        }

        // The coupon discount is the remaining difference
        $priceAfterOnlineDiscount = $basePrice - $onlineDiscountValue;
        $couponDiscount = $this->coupon_id ? ($priceAfterOnlineDiscount - $this->amount) : 0;

        // The final commission for the platform is the initial commission minus the coupon value.
        // The platform absorbs the coupon cost. This can be negative.
        $finalCommission = $initialCommission - $couponDiscount;

        // Update the invoice with the calculated amounts
        $this->update([
            'commission_amount' => round($finalCommission),
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
