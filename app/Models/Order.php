<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'email',
        'phone',
        'first_name',
        'last_name',
        'subtotal',
        'tax_total',
        'shipping_total',
        'discount_total',
        'total',
        'currency',
        'billing_address',
        'shipping_address',
        'placed_at',
        'shipped_at',
        'delivered_at',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'metadata' => 'array',
        'placed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Model booting
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * İlişkiler
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Sipariş numarası oluştur
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ILS-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Müşteri adı
     */
    public function getCustomerNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Sipariş durumunu değiştir
     */
    public function updateStatus(OrderStatus $status): bool
    {
        $oldStatus = $this->status;
        $this->status = $status;

        // Durum değişiklik zamanlarını kaydet
        match($status) {
            OrderStatus::PROCESSING => $this->placed_at = $this->placed_at ?? now(),
            OrderStatus::SHIPPED => $this->shipped_at = now(),
            OrderStatus::DELIVERED => $this->delivered_at = now(),
            default => null,
        };

        $result = $this->save();

        if ($result) {
            // Audit log kaydet
            activity()
                ->performedOn($this)
                ->withProperties([
                    'old_status' => $oldStatus->value,
                    'new_status' => $status->value,
                ])
                ->log('order_status_changed');
        }

        return $result;
    }

    /**
     * Ödeme durumunu değiştir
     */
    public function updatePaymentStatus(PaymentStatus $status): bool
    {
        $oldStatus = $this->payment_status;
        $this->payment_status = $status;

        $result = $this->save();

        if ($result) {
            // Audit log kaydet
            activity()
                ->performedOn($this)
                ->withProperties([
                    'old_payment_status' => $oldStatus->value,
                    'new_payment_status' => $status->value,
                ])
                ->log('payment_status_changed');
        }

        return $result;
    }

    /**
     * Sipariş iptal edilebilir mi?
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::PAID,
            OrderStatus::PROCESSING,
        ]);
    }

    /**
     * Sipariş iade edilebilir mi?
     */
    public function canBeRefunded(): bool
    {
        return $this->status === OrderStatus::DELIVERED &&
               $this->payment_status === PaymentStatus::CAPTURED &&
               $this->delivered_at->diffInDays(now()) <= 14;
    }

    /**
     * Fatura adresi formatla
     */
    public function getFormattedBillingAddressAttribute(): string
    {
        $address = $this->billing_address;
        if (!$address) return '';

        return sprintf(
            "%s %s
%s
%s %s
%s/%s %s",
            $address['first_name'],
            $address['last_name'],
            $address['company'] ?? '',
            $address['address_line_1'],
            $address['address_line_2'] ?? '',
            $address['district'],
            $address['city'],
            $address['postal_code']
        );
    }

    /**
     * Kargo adresi formatla
     */
    public function getFormattedShippingAddressAttribute(): string
    {
        $address = $this->shipping_address;
        if (!$address) return '';

        return sprintf(
            "%s %s
%s
%s %s
%s/%s %s",
            $address['first_name'],
            $address['last_name'],
            $address['company'] ?? '',
            $address['address_line_1'],
            $address['address_line_2'] ?? '',
            $address['district'],
            $address['city'],
            $address['postal_code']
        );
    }
}space App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
}
