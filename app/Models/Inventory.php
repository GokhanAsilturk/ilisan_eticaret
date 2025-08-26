<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'low_stock_threshold',
        'track_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'available_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'track_quantity' => 'boolean',
    ];

    /**
     * Product variant this inventory belongs to
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Calculate available quantity automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($inventory) {
            $inventory->available_quantity = $inventory->quantity - $inventory->reserved_quantity;
        });
    }

    /**
     * Reserve stock for an order
     */
    public function reserve(int $quantity): bool
    {
        if (!$this->track_quantity) {
            return true;
        }

        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->increment('reserved_quantity', $quantity);
        $this->decrement('available_quantity', $quantity);

        return true;
    }

    /**
     * Release reserved stock
     */
    public function release(int $quantity): void
    {
        if (!$this->track_quantity) {
            return;
        }

        $this->decrement('reserved_quantity', $quantity);
        $this->increment('available_quantity', $quantity);
    }

    /**
     * Confirm stock usage (remove from total quantity)
     */
    public function confirm(int $quantity): void
    {
        if (!$this->track_quantity) {
            return;
        }

        $this->decrement('quantity', $quantity);
        $this->decrement('reserved_quantity', $quantity);
        // available_quantity stays the same
    }

    /**
     * Add stock
     */
    public function add(int $quantity): void
    {
        $this->increment('quantity', $quantity);
        $this->increment('available_quantity', $quantity);
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->track_quantity &&
               $this->available_quantity <= $this->low_stock_threshold;
    }

    /**
     * Check if out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->track_quantity && $this->available_quantity <= 0;
    }

    /**
     * Scope: low stock items
     */
    public function scopeLowStock($query)
    {
        return $query->where('track_quantity', true)
                    ->whereRaw('available_quantity <= low_stock_threshold');
    }

    /**
     * Scope: out of stock items
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('track_quantity', true)
                    ->where('available_quantity', '<=', 0);
    }
}
