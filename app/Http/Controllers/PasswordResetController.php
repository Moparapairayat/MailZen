<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $broker = $this->resolveBroker((string) $request->input('email'));

        if ($broker === null) {
            return back()->withErrors([
                'email' => __('We can\'t find a user with that email address.'),
            ]);
        }

        try {
            $status = Password::broker($broker)->sendResetLink(
                $request->only('email')
            );
        } catch (TransportExceptionInterface $e) {
            \Log::error('Password reset email transport failed', [
                'broker' => $broker,
                'email' => (string) $request->input('email'),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => __('Unable to send password reset email. Please try again later.'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Password reset email failed', [
                'broker' => $broker,
                'email' => (string) $request->input('email'),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => __('Unable to send password reset email. Please try again later.'),
            ]);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $broker = $this->resolveBroker((string) $request->input('email'));

        if ($broker === null) {
            return back()->withErrors([
                'email' => __('We can\'t find a user with that email address.'),
            ]);
        }

        $status = Password::broker($broker)->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make((string) $request->input('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', __('Your password has been reset.'));
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }

    private function resolveBroker(string $email): ?string
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }

        if (Customer::query()->where('email', $email)->exists()) {
            return 'customers';
        }

        if (User::query()->where('email', $email)->exists()) {
            return 'users';
        }

        return null;
    }
}
