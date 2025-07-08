<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use HasFactory, BelongToHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'valid_from' => 'date',
        'valid_till' => 'date',
        'status' => 'boolean',
    ];

    protected $attributes = [
        'status' => true,
    ];

    protected $fillable = [
        'code',
        'percent',
        'type',
        'valid_from',
        'valid_till',
        'limit_usage',
        'highest_value',
        'owner_id',
        'status',
        'usage_count',
        'hotel_id',
    ];

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = Str::upper($value);
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = Str::upper($value);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookingInvoices()
    {
        return $this->hasMany(BookingInvoice::class);
    }
}
