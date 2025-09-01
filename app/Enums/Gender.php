<?php

declare(strict_types=1);

namespace App\Enums;

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';

    /**
     * Türkçe label döndürür
     */
    public function label(): string
    {
        return match($this) {
            self::MALE => 'Erkek',
            self::FEMALE => 'Kadın',
            self::OTHER => 'Diğer',
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
     * Türkçe string'den enum'a çevir
     */
    public static function fromTurkish(string $turkish): ?self
    {
        return match(strtolower($turkish)) {
            'erkek' => self::MALE,
            'kadın', 'kadin' => self::FEMALE,
            'diğer', 'diger' => self::OTHER,
            default => null,
        };
    }
}
