<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\IyzicoTestCards;
use Illuminate\Http\JsonResponse;

/**
 * Payment test controller - development/staging ortamı için
 */
class PaymentTestController extends Controller
{
    private const DEBUG_ONLY_ERROR = 'Test endpoints only available in debug mode';

    /**
     * Test kartları listesi
     */
    public function getTestCards(): JsonResponse
    {
        if (!config('app.debug')) {
            return response()->json(['error' => self::DEBUG_ONLY_ERROR], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'test_cards' => IyzicoTestCards::getAllCards(),
                'usage_info' => [
                    'note' => 'iyzico sandbox test kartları',
                    'environment' => 'sandbox',
                    'threeds_password' => 'Not required for sandbox'
                ]
            ]
        ]);
    }

    /**
     * Payment test bilgileri
     */
    public function getPaymentConfig(): JsonResponse
    {
        if (!config('app.debug')) {
            return response()->json(['error' => self::DEBUG_ONLY_ERROR], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'gateway' => 'iyzico',
                'environment' => config('services.iyzico.base_url') === 'https://sandbox-api.iyzipay.com' ? 'sandbox' : 'production',
                'enabled' => config('services.iyzico.api_key') ? true : false,
                'callback_url' => route('payment.iyzico.callback'),
                'webhook_url' => route('payment.iyzico.webhook'),
                'currency' => 'TRY',
                'test_billing_address' => [
                    'name' => 'Test User',
                    'address' => 'Test Mahallesi, Test Sokak No:1',
                    'city' => 'İstanbul',
                    'postal_code' => '34000'
                ]
            ]
        ]);
    }

    /**
     * Sample payment request
     */
    public function getSamplePaymentRequest(): JsonResponse
    {
        if (!config('app.debug')) {
            return response()->json(['error' => self::DEBUG_ONLY_ERROR], 403);
        }

        $testCard = IyzicoTestCards::THREEDS_SUCCESS->getCardInfo();

        return response()->json([
            'success' => true,
            'data' => [
                'endpoint' => '/api/payment/initiate',
                'method' => 'POST',
                'headers' => [
                    'Authorization' => 'Bearer {your-auth-token}',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'sample_payload' => [
                    'order_number' => 'ORDER-TEST-001',
                    'card' => [
                        'holder_name' => $testCard['holder_name'],
                        'number' => $testCard['number'],
                        'expire_month' => $testCard['expire_month'],
                        'expire_year' => $testCard['expire_year'],
                        'cvc' => $testCard['cvc']
                    ],
                    'billing_address' => [
                        'name' => 'Test User',
                        'address' => 'Test Mahallesi, Test Sokak No:1',
                        'city' => 'İstanbul',
                        'postal_code' => '34000'
                    ]
                ]
            ]
        ]);
    }
}
