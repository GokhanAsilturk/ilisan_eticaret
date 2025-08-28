<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add security headers
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Add security headers to response
     */
    private function addSecurityHeaders(Response $response): void
    {
        // Content Security Policy
        $csp = $this->buildContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);

        // XSS Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content Type Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Frame Options
        $response->headers->set('X-Frame-Options', 'DENY');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // HSTS (HTTP Strict Transport Security)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Permissions Policy
        $response->headers->set('Permissions-Policy', $this->buildPermissionsPolicy());

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
    }

    /**
     * Build Content Security Policy
     */
    private function buildContentSecurityPolicy(): string
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net unpkg.com",
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net",
            "font-src 'self' fonts.gstatic.com",
            "img-src 'self' data: blob: *.amazonaws.com *.cloudfront.net localhost:9000",
            "media-src 'self' *.amazonaws.com *.cloudfront.net",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self' sandbox-api.iyzipay.com api.iyzipay.com",
            "frame-ancestors 'none'",
            "upgrade-insecure-requests",
        ];

        // Development specific policies
        if (config('app.debug')) {
            $policies[] = "connect-src 'self' ws://localhost:* http://localhost:*";
        }

        return implode('; ', $policies);
    }

    /**
     * Build Permissions Policy
     */
    private function buildPermissionsPolicy(): string
    {
        $policies = [
            'camera=()',
            'microphone=()',
            'geolocation=()',
            'payment=(self)',
            'usb=()',
            'magnetometer=()',
            'accelerometer=()',
            'gyroscope=()',
        ];

        return implode(', ', $policies);
    }
}
