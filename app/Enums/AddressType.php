<?php

namespace App\Enums;

enum AddressType: string
{
    case SHIPPING = 'shipping';
    case BILLING = 'billing';

    public function label(): string
    {
        return match ($this) {
            self::SHIPPING => 'Teslimat Adresi',
            self::BILLING => 'Fatura Adresi',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function (self $type) {
            return [$type->value => $type->label()];
        })->toArray();
    }
}
