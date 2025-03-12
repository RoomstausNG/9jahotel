<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\BelongToHotel;
use App\Models\Scopes\InvoiceScope;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, BelongToHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'from' => 'datetime',
        'to' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new InvoiceScope);

        parent::booted();

        self::creating(function ($invoice) {
            $hotel = Hotel::find($invoice->hotel_id);
            $invoice->reference = IdGenerator::generate(['table' => 'invoices', 'field' => 'reference', 'length' => 10, 'reset_on_prefix_change' => true, 'prefix' => 'INV' . strtoupper(substr($hotel->code, 0, 3)) .  date('y')]);
            $invoice->to = Carbon::parse($invoice->from)->addYear();
        });

        // self::created(function ($invoice) {
        //     $invoice->to = Carbon::parse($invoice->from)->addYear();
        // });
    }
}