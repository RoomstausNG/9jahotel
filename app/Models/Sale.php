<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Traits\HasManyFinance;
use App\Models\Scopes\HotelScope;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Sale extends Model
{
    use HasFactory, BelongToHotel, HasManyFinance;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(Item::class, Order::class);
    }


    static function booted()
    {
        parent::boot();

        static::addGlobalScope(new HotelScope);

        self::creating(function ($sale) {
            $sale->hotel_id = auth()->user()->hotel_id;

            $sale->code = strtoupper(IdGenerator::generate(['table' => 'sales', 'field' => 'code', 'length' => 10, 'reset_on_prefix_change' => true, 'prefix' => 'SAL' . date('d')]));
        });

        // self::created(function ($sale) {
        // });

        // self::updating(function ($sale) {
        //     $discountprice = $sale->total_price - ($sale->discount / 100) * $sale->total_price;

        //     if ($sale->discount > 0 || !empty($sale->discount)) {
        //         // $sale->update(['total_price' => $sale->quantity * $discountprice]);
        //         $sale->total_price = $sale->quantity * $discountprice;
        //     } else {
        //         // $sale->update(['total_price' => $sale->quantity * $sale->item->price]);
        //         $sale->total_price = $sale->item->price;
        //     }
        // });

        // self::updated(function ($sale) {
        //     $sale->finances()->update([
        //         'amount' => $sale->total_price,
        //         'description' => 'sales of items',
        //         'type' => 'income'
        //     ]);
        // });

        self::deleted(function ($sale) {
            $sale->finances()->delete();
        });
    }
}
