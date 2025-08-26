<?php

namespace App\Enums;

enum CouponType: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::FIXED => 'Sabit Tutar',
            self::PERCENTAGE => 'Yüzde İndirim',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function (self $type) {
            return [$type->value => $type->label()];
        })->toArray();
    }
}
