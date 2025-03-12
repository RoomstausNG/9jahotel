<?php

namespace App\Models;

use App\Models\Scopes\HotelScope;
use App\Traits\BelongToHotel;
use App\Traits\HasManyFinance;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// #[ScopedBy(HotelScope::class)]
class Revenue extends Model
{
    use HasFactory, BelongToHotel, HasManyFinance;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    static function boot()
    {
        parent::boot();

        self::deleted(function ($income) {
            $income->finances->each(fn($finance) => $finance->delete());
        });
    }
}