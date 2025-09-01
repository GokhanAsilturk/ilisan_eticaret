<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use Illuminate\Support\Str;
use Iyzipay\Options;
use Iyzipay\Model\Payment as IyzicoPayment;
use Iyzipay\Model\PaymentAuth;
use Iyzipay\Model\ThreedsInitialize;
use Iyzipay\Request\CreatePaymentRequest;
use Iyzipay\Request\CreateThreedsPaymentRequest;

/**
 * iyzico 3D Secure API entegrasyonu
 */
class IyzicoPaymentService
{
    private Options $options;

    public function __construct()
    {
        $this->options = new Options();
        $this->options->setApiKey(config('services.iyzico.api_key'));
        $this->options->setSecretKey(config('services.iyzico.secret_key'));
        $this->options->setBaseUrl(config('services.iyzico.base_url'));
    }

    /**
     * 3D Secure ödeme başlatma
     */
    public function initiate3DSecurePayment(Order $order, array $cardData, array $billingAddress): array
    {
        $request = new CreateThreedsPaymentRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($this->generateConversationId($order));
        $request->setPrice($order->total);
        $request->setPaidPrice($order->total);
        $request->setCurrency(\Iyzipay\Model\Currency::TL);
        $request->setInstallment(1);
        $request->setBasketId($order->order_number);
        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);

        // Callback URLs
        $request->setCallbackUrl(config('app.url') . '/api/payment/iyzico/callback');

        // Card information
        $paymentCard = new \Iyzipay\Model\PaymentCard();
        $paymentCard->setCardHolderName($cardData['holder_name']);
        $paymentCard->setCardNumber($cardData['number']);
        $paymentCard->setExpireMonth($cardData['expire_month']);
        $paymentCard->setExpireYear($cardData['expire_year']);
        $paymentCard->setCvc($cardData['cvc']);
        $paymentCard->setRegisterCard(0);
        $request->setPaymentCard($paymentCard);

        // Buyer information
        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId((string) $order->user_id);
        $buyer->setName($order->user->first_name);
        $buyer->setSurname($order->user->last_name);
        $buyer->setGsmNumber($order->user->phone);
        $buyer->setEmail($order->user->email);
        $buyer->setIdentityNumber('11111111111'); // Test için
        $buyer->setLastLoginDate(date('Y-m-d H:i:s'));
        $buyer->setRegistrationDate($order->user->created_at->format('Y-m-d H:i:s'));
        $buyer->setRegistrationAddress($billingAddress['address']);
        $buyer->setIp(request()->ip());
        $buyer->setCity($billingAddress['city']);
        $buyer->setCountry('Turkey');
        $buyer->setZipCode($billingAddress['postal_code']);
        $request->setBuyer($buyer);

        // Shipping and billing addresses
        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($order->shipping_first_name . ' ' . $order->shipping_last_name);
        $shippingAddress->setCity($order->shipping_city);
        $shippingAddress->setCountry('Turkey');
        $shippingAddress->setAddress($order->shipping_address_line_1);
        $shippingAddress->setZipCode($order->shipping_postal_code);
        $request->setShippingAddress($shippingAddress);

        $billingAddressObj = new \Iyzipay\Model\Address();
        $billingAddressObj->setContactName($billingAddress['name']);
        $billingAddressObj->setCity($billingAddress['city']);
        $billingAddressObj->setCountry('Turkey');
        $billingAddressObj->setAddress($billingAddress['address']);
        $billingAddressObj->setZipCode($billingAddress['postal_code']);
        $request->setBillingAddress($billingAddressObj);

        // Basket items
        $basketItems = [];
        foreach ($order->items as $item) {
            $basketItem = new \Iyzipay\Model\BasketItem();
            $basketItem->setId((string) $item->id);
            $basketItem->setName($item->variant->product->name);
            $basketItem->setCategory1($item->variant->product->category->name);
            $basketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
            $basketItem->setPrice($item->price);
            $basketItems[] = $basketItem;
        }
        $request->setBasketItems($basketItems);

        try {
            $threedsInitialize = ThreedsInitialize::create($request, $this->options);

            if ($threedsInitialize->getStatus() === 'success') {
                // Payment record oluştur
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'gateway' => 'iyzico',
                    'gateway_transaction_id' => $threedsInitialize->getPaymentId(),
                    'status' => PaymentStatus::PENDING,
                    'amount' => $order->total,
                    'currency' => 'TRY',
                    'gateway_response' => $threedsInitialize->getRawResult(),
                    'metadata' => [
                        'conversation_id' => $request->getConversationId(),
                        'threeds_html_content' => $threedsInitialize->getHtmlContent()
                    ]
                ]);

                return [
                    'success' => true,
                    'payment_id' => $payment->id,
                    'threeds_html_content' => $threedsInitialize->getHtmlContent(),
                    'conversation_id' => $request->getConversationId()
                ];
            }

            return $this->createErrorResponse(
                $threedsInitialize->getErrorMessage(),
                $threedsInitialize->getErrorCode()
            );

        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage(), 'EXCEPTION');
        }
    }

    /**
     * 3D Secure callback işleme
     */
    public function handle3DSecureCallback(array $callbackData): array
    {
        try {
            $request = new CreateThreedsPaymentRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setConversationId($callbackData['conversationId']);
            $request->setPaymentId($callbackData['paymentId']);
            $request->setConversationData($callbackData['conversationData'] ?? '');

            $threedsPayment = \Iyzipay\Model\ThreedsPayment::create($request, $this->options);

            // Payment record'u bul ve güncelle
            $payment = Payment::where('gateway_transaction_id', $callbackData['paymentId'])->first();

            if (!$payment) {
                return $this->createErrorResponse('Payment not found', 'PAYMENT_NOT_FOUND');
            }

            return $this->processPaymentCallback($payment, $threedsPayment);

        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage(), 'CALLBACK_EXCEPTION');
        }
    }

    /**
     * İade işlemi
     */
    public function refundPayment(Payment $payment, float $amount): array
    {
        try {
            $request = new \Iyzipay\Request\CreateRefundRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setConversationId($this->generateConversationId($payment->order));
            $request->setPaymentTransactionId($payment->gateway_transaction_id);
            $request->setPrice($amount);
            $request->setIp(request()->ip());

            $refund = \Iyzipay\Model\Refund::create($request, $this->options);

            if ($refund->getStatus() === 'success') {
                $payment->update([
                    'status' => PaymentStatus::REFUNDED,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'refund_processed_at' => now(),
                        'refund_amount' => $amount,
                        'refund_response' => $refund->getRawResult()
                    ])
                ]);

                return $this->createSuccessResponse([
                    'refund_id' => $refund->getPaymentId()
                ]);
            }

            return $this->createErrorResponse(
                $refund->getErrorMessage(),
                $refund->getErrorCode()
            );

        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage(), 'REFUND_EXCEPTION');
        }
    }

    /**
     * Webhook signature doğrulama
     */
    public function validateWebhookSignature(array $webhookData, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', json_encode($webhookData), config('services.iyzico.secret_key'));

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Conversation ID oluştur
     */
    private function generateConversationId(Order $order): string
    {
        return 'order_' . $order->order_number . '_' . Str::random(8);
    }

    /**
     * Success response helper
     */
    private function createSuccessResponse(array $data): array
    {
        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Error response helper
     */
    private function createErrorResponse(string $error, string $errorCode): array
    {
        return [
            'success' => false,
            'error' => $error,
            'error_code' => $errorCode
        ];
    }

    /**
     * Payment callback işleme helper
     */
    private function processPaymentCallback(Payment $payment, $threedsPayment): array
    {
        if ($threedsPayment->getStatus() === 'success') {
            $payment->update([
                'status' => PaymentStatus::CAPTURED,
                'gateway_response' => $threedsPayment->getRawResult(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'callback_processed_at' => now()
                ])
            ]);

            $payment->order->update([
                'status' => \App\Enums\OrderStatus::PAID
            ]);

            return $this->createSuccessResponse([
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id
            ]);
        }

        $payment->update([
            'status' => PaymentStatus::FAILED,
            'gateway_response' => $threedsPayment->getRawResult(),
        ]);

        return $this->createErrorResponse(
            $threedsPayment->getErrorMessage(),
            $threedsPayment->getErrorCode()
        );
    }
}
