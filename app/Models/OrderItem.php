<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'variant_id',
        'product_name',
        'variant_name',
        'product_sku',
        'variant_sku',
        'quantity',
        'price',
        'total',
        'variant_attributes',
        'weight',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'weight' => 'decimal:2',
        'variant_attributes' => 'array',
    ];

    /**
     * İlişkiler
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Ürün görselini al
     */
    public function getProductImageAttribute(): ?string
    {
        if (!$this->variant || !$this->variant->product) {
            return null;
        }

        $color = $this->variant_attributes['color'] ?? null;

        $media = $this->variant->product->media()
            ->when($color, fn($q) => $q->where('attributes->color', $color))
            ->where('attributes->is_primary', true)
            ->first();

        return $media ? $media->getUrl() : null;
    }

    /**
     * Varyant bilgilerini formatla
     */
    public function getFormattedVariantAttribute(): string
    {
        if (!$this->variant_attributes) {
            return $this->variant_name;
        }

        $attributes = [];
        foreach ($this->variant_attributes as $key => $value) {
            $attributes[] = ucfirst($key) . ': ' . $value;
        }

        return $this->variant_name . ' (' . implode(', ', $attributes) . ')';
    }

    /**
     * Birim ağırlığı al
     */
    public function getUnitWeightAttribute(): float
    {
        return $this->weight ?? $this->variant?->weight ?? 0;
    }

    /**
     * Toplam ağırlığı al
     */
    public function getTotalWeightAttribute(): float
    {
        return $this->unit_weight * $this->quantity;
    }

    /**
     * Cart item'dan order item oluştur
     */
    public static function createFromCartItem(CartItem $cartItem, Order $order): self
    {
        $variant = $cartItem->variant;
        $product = $variant->product;

        return self::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'product_sku' => $product->sku,
            'variant_sku' => $variant->sku,
            'quantity' => $cartItem->quantity,
            'price' => $cartItem->price,
            'total' => $cartItem->total,
            'variant_attributes' => $variant->attributes,
            'weight' => $variant->weight,
        ]);
    }
}space App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //
}
