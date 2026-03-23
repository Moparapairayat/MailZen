<?php

namespace App\Http\Middleware;

use App\Services\AffiliateService;
use Closure;
use Illuminate\Http\Request;

class CaptureAffiliateReferral
{
    public function handle(Request $request, Closure $next)
    {
        app(AffiliateService::class)->captureReferral($request);

        return $next($request);
    }
}
