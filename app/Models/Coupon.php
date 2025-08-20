<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

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

    protected $casts = [
        'valid_from' => 'date',
        'valid_till' => 'date',
        'highest_value' => 'decimal:2',
        'status' => 'boolean',
    ];

    /**
     * Get the hotel that owns the coupon.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the owner of the coupon.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Check if the coupon is valid for use.
     */
    public function isValid(): bool
    {
        // Check if coupon is active
        if (!$this->status) {
            return false;
        }

        // Check date validity
        $today = now()->toDateString();
        if ($today < $this->valid_from || $today > $this->valid_till) {
            return false;
        }

        // Check usage limit (skip if unlimited usage: -1)
        if ($this->limit_usage !== -1 && $this->usage_count >= $this->limit_usage) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the discount amount for a given price.
     */
    public function calculateDiscount(float $originalPrice): float
    {
        if (!$this->isValid()) {
            return 0.0;
        }

        // Calculate percentage discount
        $percentageDiscount = $originalPrice * ($this->percent / 100);

        // Apply highest value limit if set
        if ($this->highest_value && $percentageDiscount > (float)$this->highest_value) {
            return (float)$this->highest_value;
        }

        return $percentageDiscount;
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get validation error message.
     */
    public function getValidationMessage(): string
    {
        if (!$this->status) {
            return 'This coupon is no longer active.';
        }

        $today = now()->toDateString();
        if ($today < $this->valid_from) {
            return 'This coupon is not yet valid.';
        }

        if ($today > $this->valid_till) {
            return 'This coupon has expired.';
        }

        if ($this->limit_usage !== -1 && $this->usage_count >= $this->limit_usage) {
            return 'This coupon has reached its usage limit.';
        }

        return 'This coupon is invalid.';
    }

    /**
     * Scope to find valid coupons.
     */
    public function scopeValid($query)
    {
        $today = now()->toDateString();
        
        return $query->where('status', 1)
                    ->where('valid_from', '<=', $today)
                    ->where('valid_till', '>=', $today)
                    ->where(function ($q) {
                        $q->where('limit_usage', -1)
                          ->orWhereRaw('usage_count < limit_usage');
                    });
    }

    /**
     * Scope to find coupons by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}
