<?php

namespace App\Services;

use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Notifications\ConfirmSubscriptionNotification;
use App\Notifications\WelcomeSubscriberNotification;
use App\Services\DeliveryServerService;
use App\Services\EmailVerificationService;
use App\Services\AutomationTriggerService;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ListSubscriberService
{
    public function query(EmailList $emailList, array $filters = [])
    {
        $query = ListSubscriber::where('list_id', $emailList->id);

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * Get paginated list of subscribers for an email list.
     */
    public function getPaginated(EmailList $emailList, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($emailList, $filters)->latest()->paginate($perPage);
    }

    /**
     * Create a new subscriber.
     */
    public function create(EmailList $emailList, array $data): ListSubscriber
    {
        $requiresConfirmation = $emailList->double_opt_in || $emailList->opt_in === 'double';
        $normalizedEmail = strtolower(trim($data['email']));
        
        try {
            $subscriber = ListSubscriber::create([
                'list_id' => $emailList->id,
                'email' => $normalizedEmail,
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'status' => $requiresConfirmation ? 'unconfirmed' : 'confirmed',
                'source' => $data['source'] ?? 'web',
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'subscribed_at' => $data['subscribed_at'] ?? now(),
                'custom_fields' => $data['custom_fields'] ?? [],
                'tags' => $data['tags'] ?? [],
                'confirmed_at' => $requiresConfirmation ? null : now(),
            ]);
        } catch (QueryException $e) {
            if ($this->isDuplicateSubscriberException($e)) {
                $existing = ListSubscriber::withTrashed()
                    ->where('list_id', $emailList->id)
                    ->where('email', $normalizedEmail)
                    ->firstOrFail();

                $shouldResubscribe = (method_exists($existing, 'trashed') && $existing->trashed())
                    || $existing->status === 'unsubscribed';

                if ($shouldResubscribe && $existing->status !== 'blacklisted') {
                    return $this->resubscribe($emailList, $existing, $data);
                }

                return $existing;
            }

            throw $e;
        }

        $this->runSubscriptionFlow($emailList, $subscriber, $requiresConfirmation);

        return $subscriber;
    }

    public function resubscribe(EmailList $emailList, ListSubscriber $subscriber, array $data = []): ListSubscriber
    {
        if ($subscriber->status === 'blacklisted') {
            return $subscriber->fresh();
        }

        if (method_exists($subscriber, 'trashed') && $subscriber->trashed()) {
            $subscriber->restore();
        }

        $requiresConfirmation = $emailList->double_opt_in || $emailList->opt_in === 'double';

        $subscriber->update([
            'first_name' => $data['first_name'] ?? $subscriber->first_name,
            'last_name' => $data['last_name'] ?? $subscriber->last_name,
            'status' => $requiresConfirmation ? 'unconfirmed' : 'confirmed',
            'source' => $data['source'] ?? $subscriber->source ?? 'web',
            'ip_address' => $data['ip_address'] ?? $subscriber->ip_address ?? request()->ip(),
            'subscribed_at' => $data['subscribed_at'] ?? now(),
            'custom_fields' => $data['custom_fields'] ?? $subscriber->custom_fields ?? [],
            'tags' => $data['tags'] ?? $subscriber->tags ?? [],
            'confirmed_at' => $requiresConfirmation ? null : now(),
            'unsubscribed_at' => null,
        ]);

        $subscriber = $subscriber->fresh();
        $this->runSubscriptionFlow($emailList, $subscriber, $requiresConfirmation);

        return $subscriber;
    }

    protected function runSubscriptionFlow(EmailList $emailList, ListSubscriber $subscriber, bool $requiresConfirmation): void
    {
        if ($requiresConfirmation) {
            $verificationService = app(EmailVerificationService::class);
            $verification = $verificationService->createVerification($subscriber, 'list_subscription');
            
            try {
                $this->configureMailerForNotification($emailList);
                
                $subscriber->notify(new ConfirmSubscriptionNotification(
                    $emailList,
                    $subscriber,
                    $verification->token
                ));
            } catch (\Exception $e) {
                \Log::error('Failed to send confirmation email: ' . $e->getMessage());
            }
        } else {
            if ($emailList->welcome_email_enabled) {
                try {
                    $this->configureMailerForNotification($emailList);
                    
                    $subscriber->notify(new WelcomeSubscriberNotification($emailList, $subscriber));
                } catch (\Exception $e) {
                    \Log::error('Failed to send welcome email: ' . $e->getMessage());
                }
            }
        }

        app(EmailListService::class)->updateSubscriberCounts($emailList);

        try {
            app(AutoResponderTriggerService::class)->triggerSubscriberEvent('subscriber_added', $subscriber);

            if ((string) ($subscriber->source ?? '') !== 'automation') {
                app(AutomationTriggerService::class)->triggerSubscriberEvent('subscriber_added', $subscriber);
            }

            if (!$requiresConfirmation) {
                app(AutoResponderTriggerService::class)->triggerSubscriberEvent('subscriber_confirmed', $subscriber);
                if ((string) ($subscriber->source ?? '') !== 'automation') {
                    app(AutomationTriggerService::class)->triggerSubscriberEvent('subscriber_confirmed', $subscriber);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to trigger autoresponders for subscriber create', [
                'subscriber_id' => $subscriber->id,
                'list_id' => $subscriber->list_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function isDuplicateSubscriberException(QueryException $e): bool
    {
        $errorInfo = $e->errorInfo;
        if (!is_array($errorInfo) || count($errorInfo) < 2) {
            return false;
        }

        return $errorInfo[0] === '23000' && (int) $errorInfo[1] === 1062;
    }

    /**
     * Update an existing subscriber.
     */
    public function update(ListSubscriber $subscriber, array $data): ListSubscriber
    {
        $subscriber->update($data);
        return $subscriber->fresh();
    }

    /**
     * Delete a subscriber.
     */
    public function delete(ListSubscriber $subscriber): bool
    {
        $emailList = $subscriber->list;
        $deleted = $subscriber->delete();
        
        if ($deleted) {
            app(EmailListService::class)->updateSubscriberCounts($emailList);
        }
        
        return $deleted;
    }

    /**
     * Confirm a subscriber.
     */
    public function confirm(ListSubscriber $subscriber): ListSubscriber
    {
        $subscriber->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Send welcome email if enabled
        if ($subscriber->list->welcome_email_enabled) {
            $subscriber->notify(new WelcomeSubscriberNotification($subscriber->list, $subscriber));
        }

        app(EmailListService::class)->updateSubscriberCounts($subscriber->list);

        try {
            app(AutoResponderTriggerService::class)->triggerSubscriberEvent('subscriber_confirmed', $subscriber->fresh());
            if ((string) ($subscriber->source ?? '') !== 'automation') {
                app(AutomationTriggerService::class)->triggerSubscriberEvent('subscriber_confirmed', $subscriber->fresh());
            }
        } catch (\Throwable $e) {
            Log::error('Failed to trigger autoresponders for subscriber confirm', [
                'subscriber_id' => $subscriber->id,
                'list_id' => $subscriber->list_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $subscriber->fresh();
    }

    /**
     * Unsubscribe a subscriber.
     */
    public function unsubscribe(ListSubscriber $subscriber): ListSubscriber
    {
        $subscriber->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        app(EmailListService::class)->updateSubscriberCounts($subscriber->list);

        try {
            app(AutoResponderTriggerService::class)->triggerSubscriberEvent('subscriber_unsubscribed', $subscriber->fresh());
            if ((string) ($subscriber->source ?? '') !== 'automation') {
                app(AutomationTriggerService::class)->triggerSubscriberEvent('subscriber_unsubscribed', $subscriber->fresh());
            }
        } catch (\Throwable $e) {
            Log::error('Failed to trigger autoresponders for subscriber unsubscribe', [
                'subscriber_id' => $subscriber->id,
                'list_id' => $subscriber->list_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $subscriber->fresh();
    }

    /**
     * Import subscribers from array.
     */
    public function import(EmailList $emailList, array $subscribers): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($subscribers as $index => $subscriberData) {
            try {
                // Check if subscriber already exists
                $exists = ListSubscriber::where('list_id', $emailList->id)
                    ->where('email', $subscriberData['email'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $this->create($emailList, $subscriberData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Resend confirmation email to subscriber.
     */
    public function resendConfirmationEmail(ListSubscriber $subscriber): void
    {
        // Only send confirmation email if subscriber is unconfirmed
        if ($subscriber->status !== 'unconfirmed') {
            throw new \Exception('Subscriber is already confirmed or cannot receive confirmation email.');
        }

        $emailList = $subscriber->list;
        
        // Check if double opt-in is enabled
        $requiresConfirmation = $emailList->double_opt_in || $emailList->opt_in === 'double';
        if (!$requiresConfirmation) {
            throw new \Exception('Double opt-in is not enabled for this list.');
        }

        // Create or get existing verification token
        $verificationService = app(EmailVerificationService::class);
        
        // Invalidate old pending verifications
        \App\Models\EmailVerification::where('subscriber_id', $subscriber->id)
            ->where('status', 'pending')
            ->where('type', 'list_subscription')
            ->update(['status' => 'expired']);

        // Create new verification
        $verification = $verificationService->createVerification($subscriber, 'list_subscription');
        
        // Send confirmation email
        $subscriber->notify(new ConfirmSubscriptionNotification(
            $emailList,
            $subscriber,
            $verification->token
        ));

        Log::info("Confirmation email resent to subscriber {$subscriber->id} ({$subscriber->email})");
    }

    /**
     * Configure mailer for sending notifications.
     */
    protected function configureMailerForNotification(EmailList $emailList): void
    {
        try {
            // Try to get a delivery server for the customer
            $deliveryServer = DeliveryServer::where('status', 'active')
                ->where('use_for', true)
                ->first();

            if ($deliveryServer) {
                $deliveryServerService = app(DeliveryServerService::class);
                $deliveryServerService->configureMailFromServer($deliveryServer);
            } else {
                // Fallback to default mail configuration
                // Ensure at least a basic SMTP mailer is configured
                if (!Config::has('mail.mailers.smtp')) {
                    Config::set('mail.mailers.smtp', [
                        'transport' => 'smtp',
                        'host' => env('MAIL_HOST', 'localhost'),
                        'port' => env('MAIL_PORT', 587),
                        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                        'username' => env('MAIL_USERNAME'),
                        'password' => env('MAIL_PASSWORD'),
                        'timeout' => 30,
                    ]);
                }
                
                if (!Config::has('mail.default')) {
                    Config::set('mail.default', 'smtp');
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not configure mailer for notification: ' . $e->getMessage());
            // Continue anyway - Laravel will use default mail config
        }
    }
}

