<?php

namespace App\Models;

use App\Traits\BelongToHotel;
use App\Traits\IsCreatingHotel;
use App\Models\Scopes\HotelScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([HotelScope::class])] class CreditPayment extends Model
{
    use HasFactory, BelongToHotel, IsCreatingHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
