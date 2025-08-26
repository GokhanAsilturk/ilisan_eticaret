<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'compare_price',
        'cost_price',
        'weight',
        'requires_shipping',
        'is_active',
        'attributes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'requires_shipping' => 'boolean',
        'is_active' => 'boolean',
        'attributes' => 'array', // color, size, etc.
    ];

    /**
     * Parent product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Inventory tracking
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'variant_id');
    }

    /**
     * Cart items with this variant
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    /**
     * Order items with this variant
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }

    /**
     * Scope: only active variants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: variants in stock
     */
    public function scopeInStock($query)
    {
        return $query->whereHas('inventory', function ($q) {
            $q->where('available_quantity', '>', 0);
        });
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(int $quantity = 1): bool
    {
        return $this->inventory?->available_quantity >= $quantity;
    }

    /**
     * Get available quantity
     */
    public function getAvailableQuantity(): int
    {
        return $this->inventory?->available_quantity ?? 0;
    }

    /**
     * Get discount percentage if compare_price exists
     */
    public function getDiscountPercentage(): ?float
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return round((($this->compare_price - $this->price) / $this->compare_price) * 100, 1);
    }

    /**
     * Get variant display name (product name + variant attributes)
     */
    public function getDisplayName(): string
    {
        $baseName = $this->product->name;

        if ($this->name && $this->name !== $baseName) {
            return $baseName . ' - ' . $this->name;
        }

        if ($this->attributes && !empty($this->attributes)) {
            $attrs = collect($this->attributes)->map(function ($value, $key) {
                return ucfirst($key) . ': ' . $value;
            })->implode(', ');

            return $baseName . ' (' . $attrs . ')';
        }

        return $baseName;
    }

    /**
     * Get profit margin
     */
    public function getProfitMargin(): ?float
    {
        if (!$this->cost_price || $this->cost_price >= $this->price) {
            return null;
        }

        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }
}
