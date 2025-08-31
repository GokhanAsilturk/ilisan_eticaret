<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\StockService;
use App\Models\ProductVariant;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private StockService $stockService
    ) {}

    public function index(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart(Auth::user());
        $summary = $this->cartService->getCartSummary($cart);
        $itemCount = $this->cartService->getItemCount($cart);

        return response()->json([
            'cart' => [
                'id' => $cart->id,
                'expires_at' => $cart->expires_at,
                'items' => $cart->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'variant_id' => $item->variant_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->price * $item->quantity,
                        'variant' => [
                            'id' => $item->variant->id,
                            'name' => $item->variant->getVariantName(),
                            'sku' => $item->variant->sku,
                            'product' => [
                                'id' => $item->variant->product->id,
                                'name' => $item->variant->product->name,
                                'slug' => $item->variant->product->slug,
                                'image' => $item->variant->product->featured_image
                            ]
                        ]
                    ];
                }),
                'summary' => $summary,
                'item_count' => $itemCount
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'integer|min:1|max:10'
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);
        $quantity = $request->input('quantity', 1);

        if (!$this->stockService->isInStock($variant, $quantity)) {
            return response()->json([
                'error' => 'Ürün stokta yetersiz',
                'available_quantity' => $this->stockService->getAvailableQuantity($variant)
            ], 400);
        }

        $success = $this->cartService->addItem($variant, $quantity, Auth::user());

        if (!$success) {
            return response()->json(['error' => 'Ürün sepete eklenemedi'], 400);
        }

        return response()->json(['message' => 'Ürün sepete eklendi'], 201);
    }

    public function update(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        $cart = $this->cartService->getOrCreateCart(Auth::user());
        $cartItem = $cart->items()->findOrFail($itemId);

        $success = $this->cartService->updateQuantity($cartItem, $request->quantity);

        if (!$success) {
            return response()->json([
                'error' => 'Stok yetersiz',
                'available_quantity' => $this->stockService->getAvailableQuantity($cartItem->variant)
            ], 400);
        }

        return response()->json(['message' => 'Sepet güncellendi']);
    }

    public function destroy(int $itemId): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart(Auth::user());
        $cartItem = $cart->items()->findOrFail($itemId);

        $this->cartService->removeItem($cartItem);

        return response()->json(['message' => 'Ürün sepetten kaldırıldı']);
    }

    public function clear(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart(Auth::user());
        $this->cartService->clearCart($cart);

        return response()->json(['message' => 'Sepet temizlendi']);
    }
}
