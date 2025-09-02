<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // Enable query counting in debug mode
        $queryCount = 0;
        if (config('app.debug')) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsage = $endMemory - $startMemory;

        if (config('app.debug')) {
            $queries = DB::getQueryLog();
            $queryCount = count($queries);

            // Log slow queries
            foreach ($queries as $query) {
                if (isset($query['time']) && $query['time'] > 100) { // 100ms threshold
                    Log::warning('Slow Query Detected', [
                        'sql' => $query['sql'] ?? 'Unknown SQL',
                        'bindings' => $query['bindings'] ?? [],
                        'time' => ($query['time'] ?? 0) . 'ms',
                        'url' => $request->fullUrl(),
                    ]);
                }
            }
        }

        // Log performance metrics for slow requests
        if ($executionTime > 1000) { // 1 second threshold
            Log::warning('Slow Request Detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => round($executionTime, 2) . 'ms',
                'memory_usage' => $this->formatBytes($memoryUsage),
                'query_count' => $queryCount,
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);
        }

        // Add performance headers
        $response->headers->set('X-Execution-Time', round($executionTime, 2) . 'ms');
        $response->headers->set('X-Memory-Usage', $this->formatBytes($memoryUsage));
        $response->headers->set('X-Query-Count', $queryCount);

        return $response;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
