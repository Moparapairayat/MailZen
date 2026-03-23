<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isInstalled()) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');

        if (
            $path === 'install'
            || str_starts_with($path, 'install/')
            || str_starts_with($path, 'build/')
            || str_starts_with($path, 'cron/')
            || str_starts_with($path, 'storage/')
            || str_starts_with($path, 'public/')
            || $path === 'up'
        ) {
            return $next($request);
        }

        return redirect()->route('install.welcome');
    }

    private function isInstalled(): bool
    {
        return (bool) config('mailzen.skip_install_wizard', false)
            || is_file($this->installedPath());
    }

    private function installedPath(): string
    {
        return storage_path('app/private/installed.json');
    }
}
