<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingNotification extends Model
{
    use HasFactory, SoftDeletes, BelongToHotel;

    protected $fillable = [
        'hotel_id',
        'booking_id',
        'guest_name',
        'check_in',
        'check_out',
        'room_type',
        'total_amount',
        'source',
        'is_read'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_amount' => 'decimal:2',
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
