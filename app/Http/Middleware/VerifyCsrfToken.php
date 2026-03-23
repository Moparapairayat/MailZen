<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'subscribe/*/api',
    ];

    /**
     * Determine if the request should be excluded from CSRF verification.
     */
    protected function inExceptArray($request)
    {
        // Exclude API subscription endpoints from CSRF verification
        $path = $request->path();
        
        // Remove leading/trailing slashes for consistent matching
        $path = trim($path, '/');
        
        // Check if path matches subscribe/{slug}/api pattern (multiple variations)
        if (preg_match('#^subscribe/[^/]+/api$#', $path)) {
            return true;
        }
        
        // Check with leading slash
        if (preg_match('#^/subscribe/[^/]+/api$#', '/' . $path)) {
            return true;
        }
        
        // Also check using Laravel's is() method with wildcard
        if ($request->is('subscribe/*/api') || 
            $request->is('*/subscribe/*/api') ||
            $request->is('subscribe/*/api/*')) {
            return true;
        }
        
        // Check route name if available
        $route = $request->route();
        if ($route && $route->getName() === 'public.subscribe.api') {
            return true;
        }
        
        // Check if it's a JSON API request to the subscribe endpoint
        if ($request->wantsJson() && str_contains($path, 'subscribe') && str_contains($path, 'api')) {
            return true;
        }

        return parent::inExceptArray($request);
    }
    
    /**
     * Determine if the session and input CSRF tokens match.
     */
    protected function tokensMatch($request)
    {
        // If this is an API subscription request, skip CSRF check
        $path = trim($request->path(), '/');
        if (preg_match('#^subscribe/[^/]+/api$#', $path)) {
            return true;
        }
        
        return parent::tokensMatch($request);
    }
}

