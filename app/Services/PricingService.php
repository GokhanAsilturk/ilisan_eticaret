<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Cart;

class PricingService
{
    public function calculateVariantPrice(ProductVariant $variant): float
    {
        $basePrice = $variant->price;
        $discountPercentage = $variant->discount_percentage ?? 0;

        if ($discountPercentage > 0) {
            return $basePrice * (1 - $discountPercentage / 100);
        }

        return $basePrice;
    }

    public function calculateTax(float $amount, float $taxRate = 18.0): float
    {
        return $amount * ($taxRate / 100);
    }

    public function calculateCartTotal(Cart $cart): array
    {
        $subtotal = 0;

        foreach ($cart->items as $item) {
            $itemPrice = $this->calculateVariantPrice($item->variant);
            $subtotal += $itemPrice * $item->quantity;
        }

        $tax = $this->calculateTax($subtotal);
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total
        ];
    }

    public function calculateShippingCost(float $orderTotal, string $city = null): float
    {
        // 500 TL üzeri ücretsiz kargo
        if ($orderTotal >= 500) {
            return 0;
        }

        // Büyük şehirler için düşük kargo
        $majorCities = ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya'];
        $baseCost = in_array($city, $majorCities) ? 20 : 25;

        return $baseCost;
    }

    public function calculateFinalTotal(Cart $cart, float $shippingCost = 0): array
    {
        $cartTotal = $this->calculateCartTotal($cart);
        $finalTotal = $cartTotal['total'] + $shippingCost;

        return array_merge($cartTotal, [
            'shipping_cost' => $shippingCost,
            'final_total' => $finalTotal
        ]);
    }
}
