<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Setting;
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
        return view('customer.auth.login');
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
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        if (!(bool) Setting::get('registration_enabled', true)) {
            return redirect()
                ->route('login')
                ->with('error', __('Registration is disabled.'));
        }

        return view('customer.auth.register');
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = Customer::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_name' => $validated['company_name'] ?? null,
            'status' => 'pending', // Admin approval required
        ]);

        app(AffiliateService::class)->attributeCustomerFromRequest($customer, $request);

        $newRegisteredGroupId = Setting::get('new_registered_customer_group_id');
        $fallbackGroupId = Setting::get('default_customer_group_id');
        $groupId = $newRegisteredGroupId ?: $fallbackGroupId;

        if ($groupId && CustomerGroup::query()->whereKey((int) $groupId)->exists()) {
            $customer->customerGroups()->syncWithoutDetaching([(int) $groupId]);
        }

        Auth::guard('customer')->login($customer);

        try {
            $customer->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('verification.notice')
                ->with('error', __('Your account was created, but we could not send the verification email. Please update your mail settings and click resend verification.'));
        }

        return redirect()->route('verification.notice');
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
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
}

