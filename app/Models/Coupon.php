<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_till' => 'datetime',
        'status' => 'boolean',
    ];

    /**
     * Scope a query to only include valid coupons.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status', 1)
            ->where('valid_from', '<=', Carbon::now())
            ->where('valid_till', '>=', Carbon::now())
            ->where(function ($q) {
                $q->where('limit_usage', -1)
                    ->orWhereRaw('usage_count < limit_usage');
            });
    }

    /**
     * Scope a query to find a coupon by its code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $code
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    /**
     * Check if the coupon is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->status) {
            return false;
        }
        if ($this->valid_from->isFuture() || $this->valid_till->isPast()) {
            return false;
        }
        if ($this->limit_usage !== -1 && $this->usage_count >= $this->limit_usage) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation message for an invalid coupon.
     *
     * @return string
     */
    public function getValidationMessage(): string
    {
        if (!$this->status) {
            return 'This coupon is inactive.';
        }
        if ($this->valid_from->isFuture()) {
            return 'This coupon is not yet valid.';
        }
        if ($this->valid_till->isPast()) {
            return 'This coupon has expired.';
        }
        if ($this->limit_usage !== -1 && $this->usage_count >= $this->limit_usage) {
            return 'This coupon has reached its usage limit.';
        }
        return 'The coupon is invalid.';
    }


    /**
     * Calculate the discount for a given amount.
     *
     * @param float $originalPrice
     * @return float
     */
    public function calculateDiscount(float $originalPrice): float
    {
        $discount = $originalPrice * ($this->percent / 100);

        if ($this->highest_value !== null && $discount > $this->highest_value) {
            return (float) $this->highest_value;
        }

        return (float) $discount;
    }

    /**
     * Increment the usage count of the coupon.
     */
    public function incrementUsageCount(): void
    {
        $this->increment('usage_count');
    }
}
