<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();

        if ($customer) {
            Auth::shouldUse('customer');
        }

        if (!$customer) {
            Auth::guard('customer')->logout();
            return redirect()->route('customer.login')
                ->with('error', 'Your account is not active. Please contact support.');
        }

        // Block suspended customers
        if ($customer->status === 'suspended') {
            Auth::guard('customer')->logout();
            return redirect()->route('customer.login')
                ->with('error', 'Your account has been suspended. Please contact support.');
        }

        // Block inactive customers
        if ($customer->status === 'inactive') {
            Auth::guard('customer')->logout();
            return redirect()->route('customer.login')
                ->with('error', 'Your account is not active. Please contact support.');
        }

        // Allow pending customers but they'll see a warning
        if ($customer->status === 'pending') {
            // Continue but they'll see a warning message in the dashboard
        }

        if ($customer->isExpired()) {
            Auth::guard('customer')->logout();
            return redirect()->route('customer.login')
                ->with('error', 'Your account has expired. Please renew your subscription.');
        }

        return $next($request);
    }
}

