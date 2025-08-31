<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use App\Services\Cache\CacheService;
use App\Services\PricingService;
use App\Services\StockService;
use App\Services\CartService;
use App\Services\CheckoutService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register cache service
        $this->app->singleton(CacheService::class);
        
        // Register application services
        $this->app->singleton(PricingService::class);
        $this->app->singleton(StockService::class);
        $this->app->singleton(CartService::class);
        $this->app->singleton(CheckoutService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for database migrations
        Schema::defaultStringLength(191);

        // Performance optimizations for production
        if ($this->app->isProduction()) {
            // Prevent lazy loading in production
            Model::preventLazyLoading();

            // Prevent silently discarding attributes
            Model::preventSilentlyDiscardingAttributes();

            // Prevent accessing missing attributes
            Model::preventAccessingMissingAttributes();
        }

        // Global query performance monitoring
        if (config('app.debug')) {
            \DB::listen(function ($query) {
                if ($query->time > 1000) { // 1 second threshold
                    \Log::warning('Slow Query Detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms'
                    ]);
                }
            });
        }
    }
}
