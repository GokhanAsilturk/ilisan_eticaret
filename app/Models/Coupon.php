<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

/**
 * Coupon Model
 *
 * Handles discount coupons and promo codes
 */
class Coupon extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'is_active',
        'allowed_user_ids',
        'allowed_categories',
        'excluded_products',
        'starts_at',
        'expires_at',
    ];

    private const DECIMAL_CAST = 'decimal:2';

    protected $casts = [
        'value' => self::DECIMAL_CAST,
        'minimum_amount' => self::DECIMAL_CAST,
        'maximum_discount' => self::DECIMAL_CAST,
        'is_active' => 'boolean',
        'allowed_user_ids' => 'array',
        'allowed_categories' => 'array',
        'excluded_products' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Orders that used this coupon
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_coupons');
    }

    /**
     * Check if coupon is valid for use
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        // Check start date
        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return false;
        }

        // Check expiry date
        if ($this->expires_at && $now->isAfter($this->expires_at)) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(float $orderTotal): float
    {
        // Check minimum amount
        if ($this->minimum_amount && $orderTotal < $this->minimum_amount) {
            return 0.0;
        }

        $discount = match ($this->type) {
            'fixed' => $this->value,
            'percentage' => ($orderTotal * $this->value) / 100,
            default => 0.0,
        };

        // Apply maximum discount limit for percentage coupons
        if ($this->type === 'percentage' && $this->maximum_discount) {
            $discount = min($discount, $this->maximum_discount);
        }

        // Cannot exceed order total
        return min($discount, $orderTotal);
    }

    /**
     * Check if user can use this coupon
     */
    public function canBeUsedByUser(int $userId): bool
    {
        // Check if coupon is restricted to specific users
        if ($this->allowed_user_ids && !in_array($userId, $this->allowed_user_ids)) {
            return false;
        }

        // Check per-user usage limit
        if ($this->usage_limit_per_user > 0) {
            $userUsageCount = $this->orders()
                ->where('user_id', $userId)
                ->count();

            if ($userUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
