<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;

class EnsureSanctumCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('sanctum');

        if (!$user || !($user instanceof Customer)) {
            abort(401);
        }

        return $next($request);
    }
}
