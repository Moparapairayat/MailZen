<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ((bool) config('mailzen.skip_install_wizard', false) || is_file(storage_path('app/private/installed.json'))) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
