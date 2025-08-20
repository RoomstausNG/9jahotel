<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    // Fields per provided schema
    protected $fillable = [
        'code',
        'percent',
        'type', // user type: marketer, agent, user
        'valid_from',
        'valid_till',
        'limit_usage', // -1 means unlimited
        'highest_value',
        'owner_id',
        'status', // 1 active, 0 inactive
        'usage_count',
        'hotel_id',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_till' => 'datetime',
        'usage_count' => 'integer',
        'percent' => 'integer',
        'limit_usage' => 'integer',
        'highest_value' => 'float',
        'status' => 'integer',
        'owner_id' => 'integer',
        'hotel_id' => 'integer',
    ];

    /*
	 * Scope for searching by code (case-insensitive)
	 */
    public function scopeByCode($query, $code)
    {
        return $query->whereRaw('LOWER(code) = ?', [strtolower($code)]);
    }

    /*
	 * Scope for active/valid coupons
	 */
    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where('status', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_till')->orWhere('valid_till', '>=', $now);
            });
    }

    public function isValid(): bool
    {
        // status is stored as tinyint; active == 1
        if ((int)$this->status !== 1) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_till && $now->gt($this->valid_till)) {
            return false;
        }

        // limit_usage: -1 => unlimited
        if ($this->limit_usage !== null && (int)$this->limit_usage !== -1) {
            if (($this->usage_count ?? 0) >= (int)$this->limit_usage) {
                return false;
            }
        }

        return true;
    }

    public function getValidationMessage(): string
    {
        $now = Carbon::now();
        if ((int)$this->status !== 1) {
            return 'This coupon is inactive.';
        }
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return 'This coupon is not yet valid.';
        }
        if ($this->valid_till && $now->gt($this->valid_till)) {
            return 'This coupon has expired.';
        }
        if ($this->limit_usage !== null && (int)$this->limit_usage !== -1 && ($this->usage_count ?? 0) >= (int)$this->limit_usage) {
            return 'This coupon has reached its usage limit.';
        }

        return 'This coupon is not valid.';
    }

    /**
     * Calculate the discount amount for a given original price.
     * Supports percent-based coupons with an optional highest_value cap.
     * Returns 0 for invalid coupons.
     */
    /**
     * Calculate discount using the `percent` column and highest_value cap per requirements.
     * Returns 0 for invalid coupons.
     */
    public function calculateDiscount(float $originalPrice): float
    {
        if (!$this->isValid()) {
            return 0.0;
        }

        $percent = (int)($this->percent ?? 0);
        if ($percent <= 0) {
            return 0.0;
        }

        $discount = ($originalPrice * ($percent / 100));

        // Only apply a cap when highest_value is a positive number.
        // Some DB rows may store 0 which should mean "no cap" in this context.
        if ($this->highest_value !== null && (float)$this->highest_value > 0) {
            $discount = min($discount, (float)$this->highest_value);
        }

        return round($discount, 2);
    }

    public function incrementUsageCount(int $by = 1)
    {
        $this->usage_count = ($this->usage_count ?? 0) + $by;
        $this->save();
    }
}
