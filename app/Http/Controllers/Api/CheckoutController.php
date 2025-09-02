<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CheckoutService $checkoutService
    ) {
    }

    public function validateCart(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart(Auth::user());
        $errors = $this->checkoutService->validateCart($cart);

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 400);
        }

        $summary = $this->cartService->getCartSummary($cart);

        return response()->json([
            'valid' => true,
            'summary' => $summary,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                    'product_name' => $item->variant->product->name,
                    'variant_name' => $item->variant->getVariantName()
                ];
            })
        ]);
    }

    public function calculateShipping(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id'
        ]);

        $address = Address::findOrFail($request->address_id);
        $cart = $this->cartService->getOrCreateCart(Auth::user());

        // Address'in kullanıcıya ait olduğunu kontrol et
        if ($address->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $shipping = $this->checkoutService->calculateShipping($address, $cart);
        $cartSummary = $this->cartService->getCartSummary($cart);

        return response()->json([
            'shipping' => $shipping,
            'cart_summary' => $cartSummary,
            'total_with_shipping' => $cartSummary['total'] + $shipping['cost']
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'shipping_address_id' => 'required|exists:addresses,id',
            'billing_address_id' => 'nullable|exists:addresses,id',
            'shipping_method' => 'required|in:standard,express',
            'notes' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();
        $cart = $this->cartService->getOrCreateCart($user);

        // Validate cart
        $errors = $this->checkoutService->validateCart($cart);
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 400);
        }

        $shippingAddress = Address::where('user_id', $user->id)
            ->findOrFail($request->shipping_address_id);

        $billingAddress = $request->billing_address_id
            ? Address::where('user_id', $user->id)->findOrFail($request->billing_address_id)
            : $shippingAddress;

        try {
            $order = $this->checkoutService->createOrder(
                $cart,
                $user,
                $shippingAddress,
                $billingAddress
            );

            // Add shipping method and notes to metadata
            $metadata = $order->metadata ?? [];
            $metadata['shipping_method'] = $request->shipping_method;
            if ($request->notes) {
                $metadata['notes'] = $request->notes;
            }
            $order->update(['metadata' => $metadata]);

            // Clear cart after successful order creation
            $this->cartService->clearCart($cart);

            return response()->json([
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status->value,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getOrder(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with(['items.variant.product'])
            ->firstOrFail();

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'shipping_cost' => $order->shipping_cost,
                'total_amount' => $order->total_amount,
                'shipping_address' => $order->shipping_address,
                'billing_address' => $order->billing_address,
                'metadata' => $order->metadata,
                'created_at' => $order->created_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'variant_name' => $item->variant_name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total
                    ];
                })
            ]
        ]);
    }
}
