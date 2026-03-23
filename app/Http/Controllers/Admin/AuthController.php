<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $intendedPath = $this->intendedPath($request);

        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var User|null $user */
            $user = Auth::guard('admin')->user();
            $fallback = $this->defaultAdminRedirect($user);

            if ($this->pathIsCustomerArea($intendedPath)) {
                $request->session()->forget('url.intended');
                return redirect()->to($fallback);
            }

            return redirect()->intended($fallback);
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function defaultAdminRedirect(?User $user): string
    {
        if (!$user) {
            return route('admin.login');
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return route('admin.dashboard');
        }

        if (!method_exists($user, 'hasAdminAbility')) {
            return route('admin.login');
        }

        $candidates = [
            ['ability' => 'admin.dashboard.access', 'route' => 'admin.dashboard'],
            ['ability' => 'admin.customers.access', 'route' => 'admin.customers.index'],
            ['ability' => 'admin.support_tickets.access', 'route' => 'admin.support-tickets.index'],
            ['ability' => 'admin.campaigns.access', 'route' => 'admin.campaigns.index'],
            ['ability' => 'admin.delivery_servers.access', 'route' => 'admin.delivery-servers.index'],
            ['ability' => 'admin.bounce_servers.access', 'route' => 'admin.bounce-servers.index'],
            ['ability' => 'admin.reply_servers.access', 'route' => 'admin.reply-servers.index'],
            ['ability' => 'admin.lists.access', 'route' => 'admin.lists.index'],
            ['ability' => 'admin.customer_groups.access', 'route' => 'admin.customer-groups.index'],
            ['ability' => 'admin.users.access', 'route' => 'admin.users.index'],
            ['ability' => 'admin.settings.access', 'route' => 'admin.settings.index'],
            ['ability' => 'admin.activities.access', 'route' => 'admin.activities.index'],
            ['ability' => 'admin.accessibility_control.access', 'route' => 'admin.accessibility-control.index'],
        ];

        foreach ($candidates as $candidate) {
            if ($user->hasAdminAbility($candidate['ability'])) {
                return route($candidate['route']);
            }
        }

        return route('admin.login');
    }

    private function intendedPath(Request $request): string
    {
        $url = $request->session()->get('url.intended');
        if (!is_string($url) || trim($url) === '') {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH);
        return is_string($path) ? $path : '';
    }

    private function pathIsCustomerArea(string $path): bool
    {
        $path = trim($path);
        if ($path === '') {
            return false;
        }

        return Str::startsWith($path, '/customer') || Str::startsWith($path, '/account');
    }
}

