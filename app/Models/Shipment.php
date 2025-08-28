<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shipment Model
 *
 * Handles order shipping and tracking
 */
class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'tracking_number',
        'status',
        'carrier',
        'tracking_url',
        'tracking_data',
        'shipping_cost',
        'weight',
        'dimensions',
        'shipped_at',
        'delivered_at',
        'estimated_delivery_at',
        'notes',
    ];

    protected $casts = [
        'tracking_data' => 'array',
        'dimensions' => 'array',
        'shipping_cost' => 'decimal:2',
        'weight' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
    ];

    /**
     * Shipment belongs to an Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if shipment is delivered
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if shipment is in transit
     */
    public function isInTransit(): bool
    {
        return in_array($this->status, ['shipped', 'in_transit']);
    }

    /**
     * Get formatted tracking URL
     */
    public function getTrackingUrlAttribute(?string $value): ?string
    {
        if ($value) {
            return $value;
        }

        // Generate tracking URL based on carrier
        return match ($this->carrier) {
            'MNG' => "https://www.mngkargo.com.tr/track/{$this->tracking_number}",
            'Yurtiçi' => "https://www.yurticikargo.com/tr/online-services/track/{$this->tracking_number}",
            'PTT' => "https://gonderitakip.ptt.gov.tr/track/{$this->tracking_number}",
            default => null,
        };
    }
}
