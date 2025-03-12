<?php

namespace App\Models;

use App\Livewire\Configuration\HotelPage;
use App\Models\Scopes\HotelScope;
use App\Traits\BelongToHotel;
use App\Traits\HasManyFinance;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// #[ScopedBy(HotelPage::class)]
class Order extends Model
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

    function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    static public function allItemSales($start, $end)
    {
        return self::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);
    }

    static public function itemSales($item_id, $start, $end)
    {
        return self::where('item_id', $item_id)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);
    }

    static function boot()
    {
        parent::boot();

        static::addGlobalScope(new HotelScope);

        self::creating(function ($order) {
            $order->hotel_id = auth()->user()->hotel_id;
            // $price = Item::find($order->item_id)->price;
            // $discountprice = $price - ($order->discount / 100) * $price;
            // if ($order->discount > 0 || !empty($order->discount)) {
            //     $order->total_price = $order->quantity * $discountprice;
            //     // dd([$discountprice, $order->total_price]);
            // } else {
            //     $order->total_price = $order->quantity * $price;
            // }

            $order->code = strtoupper(IdGenerator::generate(['table' => 'orders', 'field' => 'code', 'length' => 8, 'reset_on_prefix_change' => true, 'prefix' => 'TRN']));
        });

        // self::created(function ($order) {
        //     $order->finances()->create([
        //         'amount' => $order->total_price,
        //         // 'amount' => $order->quantity * $order->item->price,
        //         'description' => 'sales of ' . $order->item->name,
        //         'type' => 'income'
        //     ]);
        // });

        // self::updating(function ($order) {
        //     $discountprice = $order->item->price - ($order->discount / 100) * $order->item->price;

        //     if ($order->discount > 0 || !empty($order->discount)) {
        //         // $order->update(['total_price' => $order->quantity * $discountprice]);
        //         $order->total_price = $order->quantity * $discountprice;
        //     } else {
        //         // $order->update(['total_price' => $order->quantity * $order->item->price]);
        //         $order->total_price = $order->quantity * $order->item->price;
        //     }
        // });

        // self::updated(function ($order) {
        //     $order->finances()->update([
        //         'amount' => $order->quantity * $order->item->price,
        //         'description' => 'sales of ' . $order->item->name,
        //         'type' => 'income'
        //     ]);
        // });

        // self::deleted(function ($order) {
        //     $order->finances()->delete();
        // });
    }
}