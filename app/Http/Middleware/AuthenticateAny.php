<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAny
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            Auth::shouldUse('admin');
            return $next($request);
        }

        if (Auth::guard('customer')->check()) {
            Auth::shouldUse('customer');
            return $next($request);
        }

        return redirect()->route('login');
    }
}
