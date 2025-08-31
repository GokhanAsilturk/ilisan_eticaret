<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\Model;

class CacheService
{
    /**
     * Cache duration constants
     */
    const SHORT_CACHE = 300; // 5 minutes
    const MEDIUM_CACHE = 1800; // 30 minutes
    const LONG_CACHE = 3600; // 1 hour
    const VERY_LONG_CACHE = 86400; // 24 hours

    /**
     * Cache tags for organized invalidation
     */
    const TAG_CATEGORIES = 'categories';
    const TAG_PRODUCTS = 'products';
    const TAG_USERS = 'users';
    const TAG_ORDERS = 'orders';
    const TAG_GLOBAL = 'global';

    /**
     * Remember cache with tags
     */
    public function remember(string $key, int $seconds, callable $callback, array $tags = [])
    {
        return Cache::tags($tags)->remember($key, $seconds, $callback);
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateByTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    /**
     * Cache categories with hierarchy
     */
    public function getCategoriesTree(): array
    {
        return $this->remember(
            'categories.tree',
            self::LONG_CACHE,
            fn() => \App\Models\Category::with('children')
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get()
                ->toArray(),
            [self::TAG_CATEGORIES]
        );
    }

    /**
     * Cache products count by category
     */
    public function getProductsCountByCategory(int $categoryId): int
    {
        return $this->remember(
            "category.{$categoryId}.products_count",
            self::MEDIUM_CACHE,
            fn() => \App\Models\Product::where('category_id', $categoryId)
                ->where('is_active', true)
                ->count(),
            [self::TAG_CATEGORIES, self::TAG_PRODUCTS]
        );
    }

    /**
     * Cache user profile with addresses
     */
    public function getUserProfile(int $userId): ?array
    {
        return $this->remember(
            "user.{$userId}.profile",
            self::MEDIUM_CACHE,
            fn() => \App\Models\User::with(['addresses', 'orders'])
                ->find($userId)
                ?->toArray(),
            [self::TAG_USERS]
        );
    }

    /**
     * Cache popular products
     */
    public function getPopularProducts(int $limit = 10): array
    {
        return $this->remember(
            "products.popular.{$limit}",
            self::LONG_CACHE,
            fn() => \App\Models\Product::with(['category', 'variants'])
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray(),
            [self::TAG_PRODUCTS]
        );
    }

    /**
     * Cache order statistics
     */
    public function getOrderStatistics(string $period = 'today'): array
    {
        $cacheKey = "orders.stats.{$period}";

        return $this->remember(
            $cacheKey,
            self::SHORT_CACHE,
            function () use ($period) {
                $query = \App\Models\Order::query();

                switch ($period) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('created_at', now()->month);
                        break;
                }

                return [
                    'total_orders' => $query->count(),
                    'total_revenue' => $query->sum('total_amount'),
                    'pending_orders' => $query->where('status', 'pending')->count(),
                    'completed_orders' => $query->where('status', 'completed')->count(),
                ];
            },
            [self::TAG_ORDERS]
        );
    }

    /**
     * Cache system settings
     */
    public function getSystemSettings(): array
    {
        return $this->remember(
            'system.settings',
            self::VERY_LONG_CACHE,
            fn() => config('app'),
            [self::TAG_GLOBAL]
        );
    }

    /**
     * Warm up essential caches
     */
    public function warmUpEssentialCaches(): void
    {
        // Warm up categories
        $this->getCategoriesTree();

        // Warm up popular products
        $this->getPopularProducts();

        // Warm up system settings
        $this->getSystemSettings();

        // Warm up order statistics
        $this->getOrderStatistics('today');
        $this->getOrderStatistics('week');
        $this->getOrderStatistics('month');

        \Log::info('Essential caches warmed up');
    }

    /**
     * Get cache key for model
     */
    public function getModelCacheKey(Model $model, string $suffix = ''): string
    {
        $key = strtolower(class_basename($model)) . '.' . $model->getKey();
        return $suffix ? "{$key}.{$suffix}" : $key;
    }

    /**
     * Cache model with relationships
     */
    public function cacheModel(Model $model, array $relations = [], int $seconds = self::MEDIUM_CACHE): Model
    {
        $key = $this->getModelCacheKey($model, 'full');
        $tags = [strtolower(class_basename($model)) . 's'];

        return $this->remember(
            $key,
            $seconds,
            fn() => $model->load($relations),
            $tags
        );
    }

    /**
     * Invalidate model cache
     */
    public function invalidateModel(Model $model): void
    {
        $tag = strtolower(class_basename($model)) . 's';
        $this->invalidateByTags([$tag]);
    }

    /**
     * Get Redis info for monitoring
     */
    public function getRedisInfo(): array
    {
        try {
            $redis = Redis::connection();
            return [
                'memory' => $redis->info('memory'),
                'stats' => $redis->info('stats'),
                'keyspace' => $redis->info('keyspace'),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Clear all cache
     */
    public function clearAll(): void
    {
        Cache::flush();
        \Log::info('All caches cleared');
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $redis = $this->getRedisInfo();

        return [
            'redis_info' => $redis,
            'laravel_cache_store' => config('cache.default'),
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
