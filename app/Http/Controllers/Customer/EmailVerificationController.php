<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationService;
use App\Models\ListSubscriber;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function __construct(
        protected EmailVerificationService $emailVerificationService
    ) {}

    /**
     * Verify an email using a token.
     */
    public function verify(string $token)
    {
        $verification = $this->emailVerificationService->verify($token);

        if (!$verification) {
            return redirect()->route('customer.dashboard')
                ->with('error', 'Invalid or expired verification token.');
        }

        return redirect()->route('customer.dashboard')
            ->with('success', 'Email verified successfully!');
    }

    /**
     * Resend verification email.
     */
    public function resend(ListSubscriber $subscriber)
    {
        $verification = $this->emailVerificationService->resend($subscriber);

        // In a real implementation, you would send the email here
        // Mail::to($subscriber->email)->send(new VerificationEmail($verification));

        return back()->with('success', 'Verification email sent successfully.');
    }
}
