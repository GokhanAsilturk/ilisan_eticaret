<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * iyzico Test kartları
 */
enum IyzicoTestCards: string
{
    // Başarılı kart
    case SUCCESS_CARD = '5528790000000008';

    // 3D Secure başarılı kart
    case THREEDS_SUCCESS = '5890040000000016';

    // Yetersiz bakiye
    case INSUFFICIENT_FUNDS = '5451030000000000';

    // Expired kart
    case EXPIRED_CARD = '4355084355084358';

    // Sahte kart
    case FRAUD_CARD = '4129111111111111';

    // 3D Secure başarısız
    case THREEDS_FAILED = '4546711234567894';

    private const DEFAULT_HOLDER_NAME = 'Test User';
    private const DEFAULT_CVC = '123';
    private const DEFAULT_MONTH = '12';
    private const DEFAULT_YEAR = '2030';

    /**
     * Test kartı bilgileri döndürür
     */
    public function getCardInfo(): array
    {
        return match($this) {
            self::SUCCESS_CARD => [
                'number' => '5528790000000008',
                'expire_month' => self::DEFAULT_MONTH,
                'expire_year' => self::DEFAULT_YEAR,
                'cvc' => self::DEFAULT_CVC,
                'holder_name' => self::DEFAULT_HOLDER_NAME,
                'description' => 'Başarılı ödeme kartı'
            ],
            self::THREEDS_SUCCESS => [
                'number' => '5890040000000016',
                'expire_month' => self::DEFAULT_MONTH,
                'expire_year' => self::DEFAULT_YEAR,
                'cvc' => self::DEFAULT_CVC,
                'holder_name' => self::DEFAULT_HOLDER_NAME,
                'description' => '3D Secure başarılı kart'
            ],
            self::INSUFFICIENT_FUNDS => [
                'number' => '5451030000000000',
                'expire_month' => self::DEFAULT_MONTH,
                'expire_year' => self::DEFAULT_YEAR,
                'cvc' => self::DEFAULT_CVC,
                'holder_name' => self::DEFAULT_HOLDER_NAME,
                'description' => 'Yetersiz bakiye kartı'
            ],
            self::EXPIRED_CARD => [
                'number' => '4355084355084358',
                'expire_month' => self::DEFAULT_MONTH,
                'expire_year' => '2020',
                'cvc' => self::DEFAULT_CVC,
                'holder_name' => self::DEFAULT_HOLDER_NAME,
                'description' => 'Süresi dolmuş kart'
            ],
            self::FRAUD_CARD => [
                'number' => '4129111111111111',
                'expire_month' => self::DEFAULT_MONTH,
                'expire_year' => self::DEFAULT_YEAR,
                'cvc' => self::DEFAULT_CVC,
                'holder_name' => self::DEFAULT_HOLDER_NAME,
                'description' => 'Sahte kart'
            ],
            self::THREEDS_FAILED => [
                'number' => '4546711234567894',
                'expire_month' => self::DEFAULT_MONTH,
                'expire_year' => self::DEFAULT_YEAR,
                'cvc' => self::DEFAULT_CVC,
                'holder_name' => self::DEFAULT_HOLDER_NAME,
                'description' => '3D Secure başarısız kart'
            ]
        };
    }

    /**
     * Tüm test kartları listesi
     */
    public static function getAllCards(): array
    {
        $cards = [];
        foreach (self::cases() as $card) {
            $cards[$card->value] = $card->getCardInfo();
        }
        return $cards;
    }
}
