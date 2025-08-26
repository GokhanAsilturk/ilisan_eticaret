<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Beklemede',
            self::PAID => 'Ödendi',
            self::PROCESSING => 'İşleniyor',
            self::SHIPPED => 'Kargoya Verildi',
            self::DELIVERED => 'Teslim Edildi',
            self::CANCELLED => 'İptal Edildi',
            self::REFUNDED => 'İade Edildi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PAID => 'info',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'secondary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::REFUNDED => 'gray',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function (self $status) {
            return [$status->value => $status->label()];
        })->toArray();
    }
}
