<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerEmailVerifiedIfRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return $next($request);
        }

        $requireVerification = (bool) Setting::get('new_register_requires_email_verification', true);

        if ($requireVerification && method_exists($customer, 'hasVerifiedEmail') && !$customer->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with('error', 'You need to verify email.');
        }

        return $next($request);
    }
}
