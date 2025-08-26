<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case EXCEPTION = 'exception';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Beklemede',
            self::PROCESSING => 'İşleniyor',
            self::SHIPPED => 'Kargoya Verildi',
            self::IN_TRANSIT => 'Yolda',
            self::DELIVERED => 'Teslim Edildi',
            self::EXCEPTION => 'Sorun Var',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'primary',
            self::SHIPPED => 'info',
            self::IN_TRANSIT => 'secondary',
            self::DELIVERED => 'success',
            self::EXCEPTION => 'danger',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function (self $status) {
            return [$status->value => $status->label()];
        })->toArray();
    }
}
