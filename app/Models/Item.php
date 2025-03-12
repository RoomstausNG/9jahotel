<?php

namespace App\Models;

use App\Models\Scopes\HotelScope;
use App\Traits\BelongToHotel;
use App\Traits\IsCreatingHotel;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// #[ScopedBy(HotelScope::class)]
class Item extends Model
{
    use HasFactory, BelongToHotel;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function revenueSource(): BelongsTo
    {
        return $this->belongsTo(Revenue::class, 'revenue_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function generalItem(): BelongsTo
    {
        return $this->belongsTo(GeneralItem::class);
    }
}
