<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasGroupAccess
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login');
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        $resolvedAbility = $this->resolveAbility($request, $ability);
        $action = $request->route()?->getActionMethod();

        if (method_exists($user, 'hasAdminAbility')) {
            if ($user->hasAdminAbility($resolvedAbility)) {
                return $next($request);
            }

            $legacyAbility = $this->resolveLegacyAbility($resolvedAbility);
            if ($legacyAbility !== null && $user->hasAdminAbility($legacyAbility)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            abort(403, $this->messageForAbility($resolvedAbility));
        }

        $method = strtoupper($request->getMethod());
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            $redirect = redirect()->back()->with('error', $this->messageForAbility($resolvedAbility));
            if ($method !== 'DELETE') {
                $redirect = $redirect->withInput();
            }

            return $redirect;
        }

        abort(403, $this->messageForAbility($resolvedAbility));
    }

    private function messageForAbility(string $ability): string
    {
        if (str_ends_with($ability, '.access')) {
            return 'You do not have access to this page.';
        }

        if (str_ends_with($ability, '.create')) {
            return 'You cannot create this item.';
        }

        if (str_ends_with($ability, '.edit')) {
            return 'You cannot edit this item.';
        }

        if (str_ends_with($ability, '.delete')) {
            return 'You cannot delete this item.';
        }

        return 'You do not have permission to perform this action.';
    }

    private function resolveLegacyAbility(string $ability): ?string
    {
        if (str_ends_with($ability, '.access')) {
            return substr($ability, 0, -strlen('.access')) . '.view';
        }

        if (str_ends_with($ability, '.edit')) {
            return substr($ability, 0, -strlen('.edit')) . '.update';
        }

        return null;
    }

    private function resolveAbility(Request $request, string $ability): string
    {
        $action = $request->route()?->getActionMethod();

        if (!$action) {
            return $ability;
        }

        if (
            str_ends_with($ability, '.access') ||
            str_ends_with($ability, '.create') ||
            str_ends_with($ability, '.edit') ||
            str_ends_with($ability, '.delete') ||
            str_ends_with($ability, '.view') ||
            str_ends_with($ability, '.update')
        ) {
            return $ability;
        }

        return match ($action) {
            'index', 'show' => $ability . '.access',
            'create' => $ability . '.access',
            'store' => $ability . '.create',
            'edit' => $ability . '.access',
            'update' => $ability . '.edit',
            'destroy' => $ability . '.delete',
            default => $ability,
        };
    }
}
