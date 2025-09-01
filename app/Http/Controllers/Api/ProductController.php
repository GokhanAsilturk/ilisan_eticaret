<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private StockService $stockService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category', 'media', 'variants'])
            ->where('is_active', true);

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('short_description', 'ILIKE', "%{$search}%");
            });
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }

        if ($request->filled('max_price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        // Sorting
        $sortBy = $request->input('sort', 'name');
        $sortOrder = $request->input('order', 'asc');

        $allowedSorts = ['name', 'created_at', 'price'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'price') {
                $query->orderBy(
                    ProductVariant::select('price')
                        ->whereColumn('product_id', 'products.id')
                        ->orderBy('price')
                        ->limit(1),
                    $sortOrder
                );
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $perPage = min($request->input('per_page', 12), 48);
        $products = $query->paginate($perPage);

        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total()
            ]
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'media', 'variants.media'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $variants = $product->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->getVariantName(),
                'attributes' => $variant->attributes,
                'price' => $variant->price,
                'sku' => $variant->sku,
                'stock_quantity' => $variant->stock_quantity,
                'is_in_stock' => $this->stockService->isInStock($variant->id, 1),
                'images' => $variant->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'url' => $media->getUrl(),
                        'thumb_url' => $media->getUrl('thumb'),
                        'alt_text' => $media->getCustomProperty('alt_text')
                    ];
                })
            ];
        });

        $images = $product->media->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
                'alt_text' => $media->getCustomProperty('alt_text')
            ];
        });

        // Available attribute options
        $attributeOptions = [];
        foreach ($product->variants as $variant) {
            foreach ($variant->attributes ?? [] as $key => $value) {
                if (!isset($attributeOptions[$key])) {
                    $attributeOptions[$key] = [];
                }
                if (!in_array($value, $attributeOptions[$key])) {
                    $attributeOptions[$key][] = $value;
                }
            }
        }

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'meta_title' => $product->meta_title,
                'meta_description' => $product->meta_description,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug
                ],
                'images' => $images,
                'variants' => $variants,
                'attribute_options' => $attributeOptions,
                'created_at' => $product->created_at
            ]
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'media', 'variants'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'products' => $products->map(function ($product) {
                $minPrice = $product->variants->min('price');
                $maxPrice = $product->variants->max('price');

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'short_description' => $product->short_description,
                    'image' => $product->media->first()?->getUrl(),
                    'price_range' => [
                        'min' => $minPrice,
                        'max' => $maxPrice,
                        'formatted' => $minPrice === $maxPrice
                            ? number_format($minPrice, 2) . ' TL'
                            : number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2) . ' TL'
                    ],
                    'category' => $product->category->name
                ];
            })
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image_path
                ];
            })
        ]);
    }

    public function variant(int $variantId): JsonResponse
    {
        $variant = ProductVariant::query()
            ->with(['product.category', 'media'])
            ->findOrFail($variantId);

        if (!$variant->product->is_active) {
            return response()->json(['error' => 'Product not available'], 404);
        }

        return response()->json([
            'variant' => [
                'id' => $variant->id,
                'name' => $variant->getVariantName(),
                'attributes' => $variant->attributes,
                'price' => $variant->price,
                'sku' => $variant->sku,
                'stock_quantity' => $variant->stock_quantity,
                'is_in_stock' => $this->stockService->isInStock($variant->id, 1),
                'product' => [
                    'id' => $variant->product->id,
                    'name' => $variant->product->name,
                    'slug' => $variant->product->slug
                ],
                'images' => $variant->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'url' => $media->getUrl(),
                        'thumb_url' => $media->getUrl('thumb'),
                        'alt_text' => $media->getCustomProperty('alt_text')
                    ];
                })
            ]
        ]);
    }
}
