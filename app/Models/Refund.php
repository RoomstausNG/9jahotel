<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refund extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function bookingInvoice(): BelongsTo
    {
        return $this->belongsTo(BookingInvoice::class, 'booking_invoice_id');
    }
}