<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        $maxAttempts = $this->getMaxAttempts($request);
        $decayMinutes = $this->getDecayMinutes();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            RateLimiter::retriesLeft($key, $maxAttempts)
        );
    }

    /**
     * Resolve request signature
     */
    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return 'api-user:' . $user->id;
        }

        return 'api-ip:' . $request->ip();
    }

    /**
     * Get the maximum number of attempts allowed
     */
    protected function getMaxAttempts(Request $request): int
    {
        if ($request->user()) {
            return 100; // Authenticated users: 100/minute
        }

        if ($request->is('api/auth/*')) {
            return 5; // Auth endpoints: 5/minute
        }

        return 60; // General API: 60/minute
    }

    /**
     * Get the decay time in minutes
     */
    protected function getDecayMinutes(): int
    {
        return 1;
    }

    /**
     * Create a 'too many attempts' response
     */
    protected function buildResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'status' => 'error',
            'message' => 'Too Many Requests',
            'code' => 429,
            'retry_after' => $retryAfter
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
        ]);
    }

    /**
     * Add rate limit headers to response
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);

        return $response;
    }
}
