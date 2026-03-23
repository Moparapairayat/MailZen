<?php

namespace App\Services;

use App\Models\EmailVerification;
use App\Models\ListSubscriber;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailVerificationService
{
    /**
     * Create a verification token for a subscriber.
     */
    public function createVerification(ListSubscriber $subscriber, string $type = 'subscription'): EmailVerification
    {
        return EmailVerification::create([
            'customer_id' => $subscriber->list->customer_id,
            'email' => $subscriber->email,
            'token' => Str::random(64),
            'type' => $type,
            'status' => 'pending',
            'list_id' => $subscriber->list_id,
            'subscriber_id' => $subscriber->id,
            'expires_at' => Carbon::now()->addDays(7),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Verify an email using a token.
     */
    public function verify(string $token): ?EmailVerification
    {
        $verification = EmailVerification::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return null;
        }

        // Mark verification as verified
        $verification->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        // Update subscriber status if it's a subscription verification
        if (in_array($verification->type, ['list_subscription', 'subscription']) && $verification->subscriber) {
            app(ListSubscriberService::class)->confirm($verification->subscriber);
        }

        return $verification;
    }

    /**
     * Resend verification email.
     */
    public function resend(ListSubscriber $subscriber): EmailVerification
    {
        // Invalidate old pending verifications
        EmailVerification::where('subscriber_id', $subscriber->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // Create new verification
        return $this->createVerification($subscriber);
    }
}

