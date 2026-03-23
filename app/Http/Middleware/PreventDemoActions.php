<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDemoActions
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    private function messageForRequest(Request $request): string
    {
        $action = $request->route()?->getActionMethod();

        if (in_array($action, ['destroy'], true)) {
            return 'You are unable to delete this in demo mode.';
        }

        if (in_array($action, ['edit', 'update'], true)) {
            return 'You are unable to edit this in demo mode.';
        }

        if (in_array($action, ['create', 'store'], true)) {
            return 'You are unable to create this in demo mode.';
        }

        return 'This action is disabled in demo mode.';
    }
}
