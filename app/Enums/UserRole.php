<?php

declare(strict_types = 1);

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * Türkçe label döndürür
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Yönetici',
            self::USER => 'Müşteri',
        };
    }

    /**
     * Tüm seçenekleri label'larıyla döndürür
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * Role rengini döndürür
     */
    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'danger',
            self::USER => 'success',
        };
    }
}
