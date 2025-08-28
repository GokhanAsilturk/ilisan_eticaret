<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class HealthController
{
    /**
     * Basic health check endpoint
     */
    public function basic(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'environment' => app()->environment(),
        ]);


    }

    /**
     * Detailed health check with dependencies
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApplication(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ];

        $overall = collect($checks)->every(fn($check) => $check['status'] === 'ok')
            ? 'healthy' : 'unhealthy';

        return response()->json([
            'status' => $overall,
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'memory_peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
            ],
        ], $overall === 'healthy' ? 200 : 503);
    }

    /**
     * API health check
     */
    public function api(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'API is healthy',
            'timestamp' => now()->toISOString(),
            'endpoints' => [
                'auth' => url('/api/auth'),
                'products' => url('/api/products'),
                'orders' => url('/api/orders'),
            ],
        ]);
    }

    /**
     * Check application status
     */
    private function checkApplication(): array
    {
        try {
            $configCached = file_exists(base_path('bootstrap/cache/config.php'));
            $routesCached = file_exists(base_path('bootstrap/cache/routes-v7.php'));

            return [
                'status' => 'ok',
                'config_cached' => $configCached,
                'routes_cached' => $routesCached,
                'debug_mode' => config('app.debug'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::selectOne('SELECT 1 as test');
            $responseTime = round((microtime(true) - $start) * 1000, 2);

            $connection = config('database.default');

            return [
                'status' => 'ok',
                'connection' => $connection,
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache system
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            $value = 'test';

            Cache::put($key, $value, 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $retrieved === $value ? 'ok' : 'error',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue system
     */
    private function checkQueue(): array
    {
        try {
            // Check if Redis connection for queues is working
            $connection = config('queue.default');

            if ($connection === 'redis') {
                Redis::connection('default')->ping();
            }

            return [
                'status' => 'ok',
                'connection' => $connection,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage system
     */
    private function checkStorage(): array
    {
        try {
            $disk = config('filesystems.default');
            $testFile = 'health_check_' . time() . '.txt';

            \Storage::disk('local')->put($testFile, 'test');
            $exists = \Storage::disk('local')->exists($testFile);
            \Storage::disk('local')->delete($testFile);

            return [
                'status' => $exists ? 'ok' : 'error',
                'default_disk' => $disk,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }
}
{
    //
}
