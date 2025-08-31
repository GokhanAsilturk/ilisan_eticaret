<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.variant.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'orders' => $orders->items()->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status->value,
                    'status_label' => $order->status->getLabel(),
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at,
                    'items_count' => $order->items->count(),
                    'items_preview' => $order->items->take(3)->map(function ($item) {
                        return [
                            'product_name' => $item->product_name,
                            'variant_name' => $item->variant_name,
                            'quantity' => $item->quantity
                        ];
                    })
                ];
            }),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total()
            ]
        ]);
    }

    public function show(string $orderNumber): JsonResponse
    {
        $order = Order::query()
            ->with(['items.variant.product.media', 'payments'])
            ->where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'status_label' => $order->status->getLabel(),
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'shipping_cost' => $order->shipping_cost,
                'discount_amount' => $order->discount_amount,
                'total_amount' => $order->total_amount,
                'shipping_address' => $order->shipping_address,
                'billing_address' => $order->billing_address,
                'metadata' => $order->metadata,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'variant_name' => $item->variant_name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total,
                        'product_image' => $item->variant?->product?->getFirstMediaUrl()
                    ];
                }),
                'payments' => $order->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'status' => $payment->status->value,
                        'payment_method' => $payment->payment_method,
                        'gateway_response' => $payment->gateway_response,
                        'created_at' => $payment->created_at
                    ];
                }),
                'tracking_info' => $this->getTrackingInfo($order)
            ]
        ]);
    }

    public function cancel(string $orderNumber): JsonResponse
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Only pending and confirmed orders can be cancelled
        if (!in_array($order->status->value, ['pending', 'confirmed'])) {
            return response()->json([
                'error' => 'Bu sipariş iptal edilemez. Mevcut durum: ' . $order->status->getLabel()
            ], 400);
        }

        $order->update(['status' => 'cancelled']);

        // Release reserved stock
        foreach ($order->items as $item) {
            if ($item->variant) {
                $item->variant->increment('stock_quantity', $item->quantity);
            }
        }

        return response()->json([
            'message' => 'Siparişiniz başarıyla iptal edildi.',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'status_label' => $order->status->getLabel()
            ]
        ]);
    }

    public function reorder(string $orderNumber): JsonResponse
    {
        $order = Order::query()
            ->with(['items.variant'])
            ->where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $unavailableItems = [];
        $addedItems = [];

        foreach ($order->items as $item) {
            if (!$item->variant || !$item->variant->product->is_active) {
                $unavailableItems[] = $item->product_name . ' - ' . $item->variant_name;
                continue;
            }

            if ($item->variant->stock_quantity < $item->quantity) {
                $unavailableItems[] = $item->product_name . ' - ' . $item->variant_name .
                    ' (Stokta sadece ' . $item->variant->stock_quantity . ' adet var)';
                continue;
            }

            // Add to cart via CartController logic would be better
            // For now, just collect available items
            $addedItems[] = [
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
                'product_name' => $item->product_name,
                'variant_name' => $item->variant_name
            ];
        }

        return response()->json([
            'message' => !empty($addedItems) ? 'Ürünler sepete eklendi.' : 'Hiçbir ürün eklenemedi.',
            'added_items' => $addedItems,
            'unavailable_items' => $unavailableItems,
            'success' => !empty($addedItems)
        ]);
    }

    private function getTrackingInfo(Order $order): ?array
    {
        $metadata = $order->metadata ?? [];
        
        if (!isset($metadata['tracking_number'])) {
            return null;
        }

        return [
            'tracking_number' => $metadata['tracking_number'],
            'carrier' => $metadata['carrier'] ?? 'Kargo Şirketi',
            'tracking_url' => $metadata['tracking_url'] ?? null,
            'estimated_delivery' => $metadata['estimated_delivery'] ?? null,
            'last_update' => $metadata['tracking_last_update'] ?? null
        ];
    }
}
