<?php

declare(strict_types = 1);

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment Model
 *
 * Handles payment transactions for orders
 */
class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'gateway_transaction_id',
        'status',
        'amount',
        'currency',
        'gateway_response',
        'metadata',
        'authorized_at',
        'captured_at',
        'failed_at',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'gateway_response' => 'array',
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'authorized_at' => 'datetime',
        'captured_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Payment belongs to an Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, [PaymentStatus::AUTHORIZED, PaymentStatus::CAPTURED]);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, [PaymentStatus::FAILED, PaymentStatus::CANCELLED]);
    }
}
