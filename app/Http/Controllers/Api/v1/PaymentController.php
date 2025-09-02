<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\IyzicoPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\InitiatePaymentRequest;

/**
 * Ödeme işlemleri API controller
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly IyzicoPaymentService $paymentService
    ) {
    }

    /**
     * 3D Secure ödeme başlatma
     */
    public function initiatePayment(InitiatePaymentRequest $request): JsonResponse
    {
        $order = Order::where('order_number', $request->order_number)
            ->where('user_id', auth()->id())
            ->with(['items.variant.product.category', 'user'])
            ->firstOrFail();

        // Sipariş durumu ve ödeme kontrolü
        $validationResult = $this->validateOrderForPayment($order);
        if (!$validationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validationResult['message']
            ], 400);
        }

        $result = $this->paymentService->initiate3DSecurePayment(
            $order,
            $request->card,
            $request->billing_address
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $result['payment_id'],
                    'threeds_html_content' => $result['threeds_html_content'],
                    'conversation_id' => $result['conversation_id']
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Ödeme başlatılamadı',
            'error_code' => $result['error_code'] ?? null
        ], 400);
    }

    /**
     * 3D Secure callback işleme
     */
    public function handle3DCallback(Request $request): JsonResponse
    {
        $callbackData = $request->all();

        $result = $this->paymentService->handle3DSecureCallback($callbackData);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Ödeme başarıyla tamamlandı',
                'data' => [
                    'payment_id' => $result['payment_id'],
                    'order_id' => $result['order_id']
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Ödeme işlemi başarısız',
            'error_code' => $result['error_code'] ?? null
        ], 400);
    }

    /**
     * Ödeme durumu sorgulama
     */
    public function getPaymentStatus(string $paymentId): JsonResponse
    {
        $payment = Payment::with('order')
            ->where('id', $paymentId)
            ->whereHas('order', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'order_number' => $payment->order->order_number,
                'status' => $payment->status->value,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'created_at' => $payment->created_at->toISOString(),
                'updated_at' => $payment->updated_at->toISOString()
            ]
        ]);
    }

    /**
     * İade talebi
     */
    public function requestRefund(Request $request, string $paymentId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'amount' => 'nullable|numeric|min:0.01'
        ]);

        $payment = Payment::with('order')
            ->where('id', $paymentId)
            ->whereHas('order', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        // İade geçerliliği kontrolü
        $validationResult = $this->validateRefundRequest($payment, $request->amount);
        if (!$validationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validationResult['message']
            ], 400);
        }

        $refundAmount = $request->amount ?? $payment->amount;
        $result = $this->paymentService->refundPayment($payment, $refundAmount);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'İade işlemi başlatıldı',
                'data' => [
                    'refund_id' => $result['data']['refund_id']
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'İade işlemi başarısız',
            'error_code' => $result['error_code'] ?? null
        ], 400);
    }

    /**
     * iyzico webhook handler
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $signature = $request->header('X-IYZ-Signature');
        $webhookData = $request->all();

        if (!$this->paymentService->validateWebhookSignature($webhookData, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Webhook işleme logic'i burada
        // Event tipine göre farklı işlemler yapılabilir

        return response()->json(['success' => true]);
    }

    /**
     * Sipariş ödeme geçerliliği kontrolü
     */
    private function validateOrderForPayment(Order $order): array
    {
        if ($order->status->value !== 'confirmed') {
            return [
                'valid' => false,
                'message' => 'Sipariş ödemesi yapılamaz durumda'
            ];
        }

        if ($order->payments()->where('status', 'captured')->exists()) {
            return [
                'valid' => false,
                'message' => 'Bu sipariş zaten ödenmiş'
            ];
        }

        return ['valid' => true];
    }

    /**
     * İade talebi geçerliliği kontrolü
     */
    private function validateRefundRequest(Payment $payment, ?float $amount): array
    {
        if ($payment->status->value !== 'captured') {
            return [
                'valid' => false,
                'message' => 'Sadece başarılı ödemeler iade edilebilir'
            ];
        }

        if ($amount && $amount > $payment->amount) {
            return [
                'valid' => false,
                'message' => 'İade miktarı ödeme miktarından fazla olamaz'
            ];
        }

        return ['valid' => true];
    }
}
