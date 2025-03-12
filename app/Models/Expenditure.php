<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Traits\HasManyFinance;
use App\Models\Scopes\HotelScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([HotelScope::class])]
class Expenditure extends Model
{
    use HasFactory, BelongToHotel, HasManyFinance;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    static function boot()
    {
        parent::boot();

        self::creating(function ($expenditure) {
            $expenditure->hotel_id = auth()->check() ? auth()->user()->hotel_id : null;
        });

        self::deleted(function ($expenditure) {
            $expenditure->finances->each(fn ($finance) => $finance->delete());
        });
    }
}