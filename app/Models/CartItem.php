<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'variant_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * İlişkiler
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Toplam tutarı hesapla
     */
    public function getTotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Ürün adını al
     */
    public function getProductNameAttribute(): string
    {
        return $this->variant->product->name ?? '';
    }

    /**
     * Varyant adını al
     */
    public function getVariantNameAttribute(): string
    {
        return $this->variant->name ?? '';
    }

    /**
     * Stok durumunu kontrol et
     */
    public function isInStock(): bool
    {
        return $this->variant &&
               $this->variant->inventory &&
               $this->variant->inventory->available_quantity >= $this->quantity;
    }

    /**
     * Maksimum satın alınabilir miktarı al
     */
    public function getMaxQuantityAttribute(): int
    {
        if (!$this->variant || !$this->variant->inventory) {
            return 0;
        }

        return $this->variant->inventory->available_quantity;
    }

    /**
     * Miktarı güncelle
     */
    public function updateQuantity(int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->delete();
        }

        if ($quantity > $this->max_quantity) {
            $quantity = $this->max_quantity;
        }

        return $this->update(['quantity' => $quantity]);
    }

    /**
     * Fiyatı güncelle (varyant fiyatı değişmişse)
     */
    public function refreshPrice(): bool
    {
        if (!$this->variant) {
            return false;
        }

        return $this->update(['price' => $this->variant->price]);
    }
}
