<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class CheckAffiliateEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if affiliate system is enabled
        $affiliateEnabled = (bool) Setting::get('affiliate_enabled', false);
        
        if (!$affiliateEnabled) {
            // For the apply route, always block if affiliate system is disabled
            if ($request->routeIs('affiliate.apply')) {
                // If it's an AJAX request, return JSON response
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Affiliate system is currently disabled.',
                        'enabled' => false
                    ], 403);
                }
                
                // For regular web requests, redirect back with error message
                return back()
                    ->with('error', 'Affiliate system is currently disabled.')
                    ->withInput();
            }
            
            // For other routes (index, payments, payout settings), 
            // allow access if the user already has an affiliate account
            if ($request->routeIs(['affiliate.index', 'affiliate.payments', 'affiliate.payout-settings.update'])) {
                $customer = $request->user('customer');
                if ($customer && $customer->affiliate()->exists()) {
                    return $next($request);
                }
            }
            
            // Block access for all other cases
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Affiliate system is currently disabled.',
                    'enabled' => false
                ], 403);
            }
            
            return back()
                ->with('error', 'Affiliate system is currently disabled.')
                ->withInput();
        }
        
        return $next($request);
    }
}
