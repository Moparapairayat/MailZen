<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureSanctumAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('sanctum');

        if (!$user || !($user instanceof User)) {
            abort(401);
        }

        return $next($request);
    }
}
