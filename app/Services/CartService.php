<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function __construct(
        private StockService $stockService,
        private PricingService $pricingService
    ) {}

    public function getOrCreateCart(?User $user = null): Cart
    {
        if ($user) {
            return Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['expires_at' => now()->addDays(30)]
            );
        }

        $sessionId = Session::getId();
        return Cart::firstOrCreate(
            ['session_id' => $sessionId],
            ['expires_at' => now()->addDays(7)]
        );
    }

    public function addItem(ProductVariant $variant, int $quantity = 1, ?User $user = null): bool
    {
        if (!$this->stockService->isInStock($variant, $quantity)) {
            return false;
        }

        $cart = $this->getOrCreateCart($user);
        $price = $this->pricingService->calculateVariantPrice($variant);

        $cartItem = $cart->items()->where('variant_id', $variant->id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if (!$this->stockService->isInStock($variant, $newQuantity)) {
                return false;
            }

            $cartItem->update([
                'quantity' => $newQuantity,
                'price' => $price
            ]);
        } else {
            $cart->items()->create([
                'variant_id' => $variant->id,
                'quantity' => $quantity,
                'price' => $price
            ]);
        }

        $cart->touch();
        return true;
    }

    public function removeItem(CartItem $cartItem): void
    {
        $cartItem->delete();
        $cartItem->cart->touch();
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): bool
    {
        if ($quantity <= 0) {
            $this->removeItem($cartItem);
            return true;
        }

        if (!$this->stockService->isInStock($cartItem->variant, $quantity)) {
            return false;
        }

        $price = $this->pricingService->calculateVariantPrice($cartItem->variant);

        $cartItem->update([
            'quantity' => $quantity,
            'price' => $price
        ]);

        $cartItem->cart->touch();
        return true;
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->delete();
    }

    public function mergeCarts(Cart $guestCart, Cart $userCart): void
    {
        foreach ($guestCart->items as $guestItem) {
            $existingItem = $userCart->items()
                ->where('variant_id', $guestItem->variant_id)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $guestItem->quantity;
                $this->updateQuantity($existingItem, $newQuantity);
            } else {
                $userCart->items()->create([
                    'variant_id' => $guestItem->variant_id,
                    'quantity' => $guestItem->quantity,
                    'price' => $guestItem->price
                ]);
            }
        }

        $this->clearCart($guestCart);
    }

    public function getCartSummary(Cart $cart): array
    {
        return $this->pricingService->calculateCartTotal($cart);
    }

    public function getItemCount(Cart $cart): int
    {
        return $cart->items->sum('quantity');
    }
}
