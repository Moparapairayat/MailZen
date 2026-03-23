<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;

class EnsureApiCustomerAbility
{
    public function handle(Request $request, Closure $next, string $ability)
    {
        $customer = $request->user('sanctum');

        if (!$customer || !($customer instanceof Customer)) {
            abort(401);
        }

        if (!method_exists($customer, 'groupAllows') || !$customer->groupAllows($ability)) {
            abort(403, 'You do not have access to this feature.');
        }

        return $next($request);
    }
}
