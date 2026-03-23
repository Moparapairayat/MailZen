<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Setting;
use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        if (!(bool) Setting::get('registration_enabled', true)) {
            return redirect()
                ->route('login')
                ->with('error', __('Registration is disabled.'));
        }

        return view('auth.register');
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

        // Try admin login first
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

        // Try customer login
        if (Auth::guard('customer')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            if ($this->pathIsAdminArea($intendedPath)) {
                $request->session()->forget('url.intended');
                return redirect()->to(route('customer.dashboard'));
            }
            return redirect()->intended(route('customer.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        if (!(bool) Setting::get('registration_enabled', true)) {
            return redirect()
                ->route('login')
                ->with('error', __('Registration is disabled.'));
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $customer = Customer::create([
            'first_name' => 'Customer',
            'last_name' => 'User',
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        app(AffiliateService::class)->attributeCustomerFromRequest($customer, $request);

        $newRegisteredGroupId = Setting::get('new_registered_customer_group_id');
        $fallbackGroupId = Setting::get('default_customer_group_id');
        $groupId = $newRegisteredGroupId ?: $fallbackGroupId;

        if ($groupId && CustomerGroup::query()->whereKey((int) $groupId)->exists()) {
            $customer->customerGroups()->syncWithoutDetaching([(int) $groupId]);
        }

        Auth::guard('customer')->login($customer);

        $requireVerification = (bool) Setting::get('new_register_requires_email_verification', true);

        if ($requireVerification) {
            try {
                $customer->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);

                return redirect()
                    ->intended(route('verification.notice'))
                    ->with('error', __('Your account was created, but we could not send the verification email. Please update your mail settings and click resend verification.'));
            }

            return redirect()->intended(route('verification.notice'));
        }

        $customer->markEmailAsVerified();
        return redirect()->intended(route('customer.dashboard'));
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        // Logout from both guards
        Auth::guard('admin')->logout();
        Auth::guard('customer')->logout();

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

    private function pathIsAdminArea(string $path): bool
    {
        $path = trim($path);
        return $path !== '' && Str::startsWith($path, '/admin');
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

