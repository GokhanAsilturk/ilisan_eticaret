<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Address;
use App\Models\User;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private CartService $cartService,
        private StockService $stockService,
        private PricingService $pricingService
    ) {}

    public function validateCart(Cart $cart): array
    {
        $errors = [];

        if ($cart->items->isEmpty()) {
            $errors[] = 'Sepet boş';
            return $errors;
        }

        foreach ($cart->items as $item) {
            if (!$this->stockService->isInStock($item->variant, $item->quantity)) {
                $errors[] = "{$item->variant->product->name} ürünü stokta yetersiz";
            }
        }

        return $errors;
    }

    public function calculateShipping(Address $address, Cart $cart): array
    {
        $cartSummary = $this->cartService->getCartSummary($cart);
        $shippingCost = $this->pricingService->calculateShippingCost($cartSummary['total'], $address->city);

        return [
            'cost' => $shippingCost,
            'estimated_days' => $this->getEstimatedDeliveryDays($address->city),
            'carrier' => 'Kargo Firması'
        ];
    }

    private function getEstimatedDeliveryDays(string $city): int
    {
        $majorCities = ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya'];

        return in_array($city, $majorCities) ? 1 : 3;
    }

    public function createOrder(Cart $cart, User $user, Address $shippingAddress, ?Address $billingAddress = null): Order
    {
        return DB::transaction(function () use ($cart, $user, $shippingAddress, $billingAddress) {
            $cartSummary = $this->cartService->getCartSummary($cart);
            $shipping = $this->calculateShipping($shippingAddress, $cart);

            foreach ($cart->items as $item) {
                if (!$this->stockService->reserveStock($item->variant, $item->quantity)) {
                    throw new \Exception("Stok rezervasyon hatası: {$item->variant->product->name}");
                }
            }

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'status' => OrderStatus::PENDING,
                'subtotal' => $cartSummary['subtotal'],
                'tax_amount' => $cartSummary['tax'],
                'shipping_cost' => $shipping['cost'],
                'total_amount' => $cartSummary['total'] + $shipping['cost'],
                'shipping_address' => $shippingAddress->toArray(),
                'billing_address' => $billingAddress ? $billingAddress->toArray() : $shippingAddress->toArray(),
                'metadata' => [
                    'shipping_info' => $shipping,
                    'cart_id' => $cart->id
                ]
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->variant->product->name,
                    'variant_name' => $item->variant->getVariantName(),
                    'product_sku' => $item->variant->product->sku,
                    'variant_sku' => $item->variant->sku,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                    'variant_attributes' => $item->variant->attributes,
                    'weight' => $item->variant->weight
                ]);
            }

            return $order;
        });
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ILS-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    public function confirmOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $this->stockService->confirmStock($item->variant, $item->quantity);
        }

        $order->update(['status' => OrderStatus::CONFIRMED]);
    }

    public function cancelOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $this->stockService->releaseStock($item->variant, $item->quantity);
        }

        $order->update(['status' => OrderStatus::CANCELLED]);
    }
}
