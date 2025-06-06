<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            $invoice->reference = strtoupper(IdGenerator::generate(['table' => 'booking_invoices', 'field' => 'reference', 'length' => 12, 'reset_on_prefix_change' => true, 'prefix' => 'RE9A' . date('d')]));
        });
    }
}
