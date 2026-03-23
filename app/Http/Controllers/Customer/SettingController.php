<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Translation\LocaleJsonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:settings.permissions.can_access_settings')->only(['index']);
        $this->middleware('customer.access:settings.permissions.can_edit_settings')->only(['update', 'updateEmail', 'updatePassword']);
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();

        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        $activeLocales = collect(app(LocaleJsonService::class)->listLocales());

        return view('customer.settings.index', compact('customer', 'timezones', 'activeLocales'));
    }

    public function update(Request $request)
    {
        $customer = $request->user('customer');

        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        $activeLocaleCodes = collect(app(LocaleJsonService::class)->listLocales())
            ->map(fn ($l) => is_object($l) && is_string($l->code ?? null) ? trim((string) $l->code) : '')
            ->filter(fn ($v) => $v !== '')
            ->values()
            ->toArray();

        $validated = $request->validate([
            'timezone' => ['required', 'string', Rule::in($timezones)],
            'language' => ['required', 'string', Rule::in($activeLocaleCodes)],
            'openai_api_key' => ['nullable', 'string', 'max:5000'],
            'gemini_api_key' => ['nullable', 'string', 'max:5000'],
            'ai_own_daily_limit' => ['nullable', 'integer', 'min:0'],
            'ai_own_monthly_limit' => ['nullable', 'integer', 'min:0'],
        ]);

        $fill = [
            'timezone' => $validated['timezone'],
            'language' => $validated['language'],
            'ai_own_daily_limit' => is_numeric($validated['ai_own_daily_limit'] ?? null) ? (int) $validated['ai_own_daily_limit'] : 0,
            'ai_own_monthly_limit' => is_numeric($validated['ai_own_monthly_limit'] ?? null) ? (int) $validated['ai_own_monthly_limit'] : 0,
        ];

        if (array_key_exists('openai_api_key', $validated) && is_string($validated['openai_api_key'])) {
            $key = trim($validated['openai_api_key']);
            if ($key !== '' && $key !== '********') {
                $fill['openai_api_key'] = $key;
            }
        }

        if (array_key_exists('gemini_api_key', $validated) && is_string($validated['gemini_api_key'])) {
            $key = trim($validated['gemini_api_key']);
            if ($key !== '' && $key !== '********') {
                $fill['gemini_api_key'] = $key;
            }
        }

        $customer->forceFill($fill)->save();

        return redirect()
            ->route('customer.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function updateEmail(Request $request)
    {
        $customer = $request->user('customer');

        $validated = $request->validateWithBag('updateEmail', [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customer?->id),
            ],
        ]);

        $newEmail = $validated['email'];

        if ($customer->email !== $newEmail) {
            $customer->forceFill([
                'email' => $newEmail,
                'email_verified_at' => null,
            ])->save();

            $customer->sendEmailVerificationNotification();
        }

        return redirect()
            ->route('customer.settings.index')
            ->with('success', 'Email updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $customer = $request->user('customer');

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($validated['current_password'], (string) $customer->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'], 'updatePassword');
        }

        $customer->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()
            ->route('customer.settings.index')
            ->with('success', 'Password updated successfully.');
    }

    public function revealSecret(Request $request, string $key)
    {
        $allowed = [
            'openai_api_key',
            'gemini_api_key',
        ];

        if (!in_array($key, $allowed, true)) {
            abort(404);
        }

        $customer = $request->user('customer');
        if (!$customer) {
            abort(404);
        }

        $value = $customer->{$key} ?? '';
        $value = is_string($value) ? trim($value) : '';

        return response()->json([
            'success' => true,
            'value' => $value,
        ]);
    }
}
