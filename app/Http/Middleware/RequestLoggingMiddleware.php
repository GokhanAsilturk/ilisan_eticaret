<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggingMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $requestId = (string) \Str::uuid();
        
        // Request logging
        $this->logRequest($request, $requestId);
        
        $response = $next($request);
        
        // Response logging
        $this->logResponse($request, $response, $startTime, $requestId);
        
        return $response;
    }

    /**
     * Log incoming request
     */
    private function logRequest(Request $request, string $requestId): void
    {
        // Skip health checks and static assets
        if ($this->shouldSkipLogging($request)) {
            return;
        }

        $data = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'user_id' => auth()->id(),
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
        ];

        // Add request body for non-GET requests (excluding sensitive data)
        if (!$request->isMethod('GET') && !$this->containsSensitiveData($request)) {
            $data['request_data'] = $request->except(['password', 'password_confirmation', '_token']);
        }

        Log::info('HTTP Request', $data);
    }

    /**
     * Log response
     */
    private function logResponse(Request $request, Response $response, float $startTime, string $requestId): void
    {
        // Skip health checks and static assets
        if ($this->shouldSkipLogging($request)) {
            return;
        }

        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $data = [
            'request_id' => $requestId,
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => $responseTime,
            'memory_usage_mb' => round(memory_get_usage() / 1024 / 1024, 2),
        ];

        // Log slow requests as warnings
        $logLevel = $responseTime > 1000 ? 'warning' : 'info';
        Log::$logLevel('HTTP Response', $data);

        // Log errors with additional context
        if ($response->getStatusCode() >= 400) {
            Log::error('HTTP Error Response', array_merge($data, [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id(),
                'response_content' => $response->getContent(),
            ]));
        }
    }

    /**
     * Check if request should be logged
     */
    private function shouldSkipLogging(Request $request): bool
    {
        $skipPatterns = [
            'health',
            'status', 
            'up',
            '_debugbar',
            'telescope',
            '*.css',
            '*.js',
            '*.png',
            '*.jpg',
            '*.jpeg',
            '*.gif',
            '*.svg',
            '*.ico',
            '*.woff',
            '*.woff2',
        ];

        foreach ($skipPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request contains sensitive data
     */
    private function containsSensitiveData(Request $request): bool
    {
        $sensitiveFields = ['password', 'token', 'secret', 'key', 'card'];
        
        foreach ($sensitiveFields as $field) {
            if ($request->has($field) || str_contains(strtolower($request->path()), $field)) {
                return true;
            }
        }

        return false;
    }
}
