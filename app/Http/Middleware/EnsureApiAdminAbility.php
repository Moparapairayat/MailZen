<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureApiAdminAbility
{
    public function handle(Request $request, Closure $next, string $ability)
    {
        $user = $request->user('sanctum');

        if (!$user || !($user instanceof User)) {
            abort(401);
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        if (!method_exists($user, 'hasAdminAbility') || !$user->hasAdminAbility($ability)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
