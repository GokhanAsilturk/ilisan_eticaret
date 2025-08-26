<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case AUTHORIZED = 'authorized';
    case CAPTURED = 'captured';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Beklemede',
            self::AUTHORIZED => 'Yetkilendirildi',
            self::CAPTURED => 'Tahsil Edildi',
            self::FAILED => 'Başarısız',
            self::REFUNDED => 'İade Edildi',
            self::CANCELLED => 'İptal Edildi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::AUTHORIZED => 'info',
            self::CAPTURED => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'gray',
            self::CANCELLED => 'secondary',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function (self $status) {
            return [$status->value => $status->label()];
        })->toArray();
    }
}
