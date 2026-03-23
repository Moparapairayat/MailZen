<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable implements MustVerifyEmail, CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, MustVerifyEmailTrait, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'google_id',
        'password',
        'timezone',
        'language',
        'status',
        'company_name',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'currency',
        'quota',
        'quota_usage',
        'max_lists',
        'max_subscribers',
        'max_campaigns',
        'expires_at',
        'stripe_customer_id',
        'avatar_path',
        'bio',
        'website_url',
        'twitter_url',
        'facebook_url',
        'linkedin_url',
        'billing_address',
        'tax_id',
        'referred_by_affiliate_id',
        'referred_at',
        'openai_api_key',
        'gemini_api_key',
        'ai_token_usage',
        'ai_image_credits_used',
        'ai_own_daily_limit',
        'ai_own_monthly_limit',
        'ai_own_daily_usage',
        'ai_own_daily_usage_date',
        'ai_own_monthly_usage',
        'ai_own_monthly_usage_month',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'openai_api_key',
        'gemini_api_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'quota' => 'decimal:2',
            'quota_usage' => 'decimal:2',
            'expires_at' => 'date',
            'last_login_at' => 'datetime',
            'billing_address' => 'array',
            'referred_at' => 'datetime',
            'ai_token_usage' => 'integer',
            'ai_image_credits_used' => 'integer',
            'ai_own_daily_limit' => 'integer',
            'ai_own_monthly_limit' => 'integer',
            'ai_own_daily_usage' => 'integer',
            'ai_own_daily_usage_date' => 'date',
            'ai_own_monthly_usage' => 'integer',
        ];
    }

    public function getOpenaiApiKeyAttribute($value): string
    {
        return $this->decryptSecretAttribute($value);
    }

    public function setOpenaiApiKeyAttribute($value): void
    {
        $this->attributes['openai_api_key'] = $this->encryptSecretAttribute($value);
    }

    public function getGeminiApiKeyAttribute($value): string
    {
        return $this->decryptSecretAttribute($value);
    }

    public function setGeminiApiKeyAttribute($value): void
    {
        $this->attributes['gemini_api_key'] = $this->encryptSecretAttribute($value);
    }

    /**
     * Get the customer's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if customer is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if customer account has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get remaining quota.
     */
    public function getRemainingQuotaAttribute(): float
    {
        return max(0, $this->quota - $this->quota_usage);
    }

    /**
     * Check if customer has available quota.
     */
    public function hasQuota(float $amount = 0): bool
    {
        return $this->remaining_quota >= $amount;
    }

    /**
     * Get the customer groups that belong to the customer.
     */
    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'customer_customer_group')
            ->withTimestamps();
    }

    public function allocatedDeliveryServers(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryServer::class, 'customer_delivery_server')
            ->withTimestamps();
    }

    /**
     * Get the affiliate account associated with the customer.
     */
    public function affiliate(): HasOne
    {
        return $this->hasOne(Affiliate::class);
    }

    /**
     * Check if customer belongs to a specific group.
     */
    public function hasCustomerGroup(string $groupName): bool
    {
        return $this->customerGroups()->where('name', $groupName)->exists();
    }

    /**
     * Check if customer has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->customerGroups()
            ->whereJsonContains('permissions', $permission)
            ->exists();
    }

    /**
     * Get a merged view of the customer's group settings.
     *
     * Merge strategy:
     * - booleans: OR (any group enables)
     * - integers: max (highest allowed)
     * - strings: first non-empty
     * - arrays: merge recursively
     */
    public function groupSettings(): array
    {
        $this->syncPlanCustomerGroup();

        $groups = $this->relationLoaded('customerGroups')
            ? $this->customerGroups
            : $this->customerGroups()->get();

        $merged = [];

        foreach ($groups as $group) {
            $groupSettings = (array) ($group->settings ?? []);

            if (empty($groupSettings)) {
                $legacySettings = [
                    'sending_quota' => [
                        'monthly_quota' => (int) ($group->quota ?? 0),
                    ],
                    'lists' => [
                        'limits' => [
                            'max_lists' => (int) ($group->max_lists ?? 0),
                            'max_subscribers' => (int) ($group->max_subscribers ?? 0),
                        ],
                    ],
                    'campaigns' => [
                        'limits' => [
                            'max_campaigns' => (int) ($group->max_campaigns ?? 0),
                        ],
                    ],
                ];

                $perm = (array) ($group->permissions ?? []);

                if (in_array('servers.permissions.can_add_delivery_servers', $perm, true)) {
                    $legacySettings['servers']['permissions']['can_add_delivery_servers'] = true;
                }
                if (in_array('servers.permissions.can_use_system_servers', $perm, true)) {
                    $legacySettings['servers']['permissions']['can_use_system_servers'] = true;
                }
                if (in_array('servers.permissions.can_select_delivery_servers_for_campaigns', $perm, true)) {
                    $legacySettings['servers']['permissions']['can_select_delivery_servers_for_campaigns'] = true;
                }
                if (in_array('servers.permissions.can_add_bounce_servers', $perm, true)) {
                    $legacySettings['servers']['permissions']['can_add_bounce_servers'] = true;
                }
                if (in_array('domains.tracking_domains.can_manage', $perm, true)) {
                    $legacySettings['domains']['tracking_domains']['can_manage'] = true;
                }
                if (in_array('domains.sending_domains.can_manage', $perm, true)) {
                    $legacySettings['domains']['sending_domains']['can_manage'] = true;
                }

                $groupSettings = $legacySettings;
            }

            $merged = $this->mergeSettingsRecursive($merged, $groupSettings);
        }

        return $merged;
    }

    private function syncPlanCustomerGroup(): void
    {
        $subscription = $this->subscriptions()->latest()->first();

        if (
            !$subscription
            || !in_array($subscription->status, ['active', 'trialing', 'past_due'], true)
        ) {
            return;
        }

        $plan = $subscription->plan;

        $stripePriceId = $subscription->stripe_price_id;
        $stripeProductId = null;

        if (is_string($stripePriceId) && \Illuminate\Support\Str::startsWith($stripePriceId, 'prod_')) {
            $stripeProductId = $stripePriceId;
            $stripePriceId = null;
        }

        if (is_string($subscription->plan_id)) {
            if (!$stripePriceId && \Illuminate\Support\Str::startsWith($subscription->plan_id, 'price_')) {
                $stripePriceId = $subscription->plan_id;
            }
            if (!$stripeProductId && \Illuminate\Support\Str::startsWith($subscription->plan_id, 'prod_')) {
                $stripeProductId = $subscription->plan_id;
            }
        }

        if (!$plan && $stripePriceId) {
            $plan = Plan::query()->where('stripe_price_id', $stripePriceId)->first();
        }

        if (!$plan && $stripeProductId) {
            $plan = Plan::query()->where('stripe_product_id', $stripeProductId)->first();
        }

        if (!$plan || !$plan->customer_group_id) {
            return;
        }

        $planGroupId = (int) $plan->customer_group_id;

        $planDerivedGroupIds = Plan::query()
            ->whereNotNull('customer_group_id')
            ->pluck('customer_group_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $currentGroupIds = $this->customerGroups()->pluck('customer_groups.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $toDetach = array_values(array_diff(array_intersect($currentGroupIds, $planDerivedGroupIds), [$planGroupId]));
        if (!empty($toDetach)) {
            $this->customerGroups()->detach($toDetach);
        }

        $this->customerGroups()->syncWithoutDetaching([$planGroupId]);

        if ($this->relationLoaded('customerGroups')) {
            $this->unsetRelation('customerGroups');
        }
    }

    /**
     * Get a specific group setting value by dot path.
     */
    public function groupSetting(string $path, mixed $default = null): mixed
    {
        return data_get($this->groupSettings(), $path, $default);
    }

    /**
     * Check if any group allows/enables a feature.
     */
    public function groupAllows(string $path): bool
    {
        $subscription = $this->subscriptions()->latest()->first();
        $subscriptionAllowsFeature = $subscription
            && in_array($subscription->status, ['active', 'trialing', 'past_due'], true)
            && in_array($path, (array) ($subscription->features ?? []), true);

        return (bool) $this->groupSetting($path, false)
            || $this->hasPermission($path)
            || $subscriptionAllowsFeature;
    }

    /**
     * Get a numeric limit from group settings. If it's 0, treat as "unlimited" (returns null).
     */
    public function groupLimit(string $path): ?int
    {
        $value = (int) $this->groupSetting($path, 0);
        if ($value > 0) {
            return $value;
        }

        $groups = $this->relationLoaded('customerGroups')
            ? $this->customerGroups
            : $this->customerGroups()->get();

        $legacy = 0;
        switch ($path) {
            case 'sending_quota.monthly_quota':
                $legacy = (int) $groups->max(fn ($g) => (int) ($g->quota ?? 0));
                break;
            case 'lists.limits.max_lists':
                $legacy = (int) $groups->max(fn ($g) => (int) ($g->max_lists ?? 0));
                break;
            case 'lists.limits.max_subscribers':
                $legacy = (int) $groups->max(fn ($g) => (int) ($g->max_subscribers ?? 0));
                break;
            case 'campaigns.limits.max_campaigns':
                $legacy = (int) $groups->max(fn ($g) => (int) ($g->max_campaigns ?? 0));
                break;
        }

        return $legacy > 0 ? $legacy : null;
    }

    /**
     * Abort (429) if the given current value would exceed a group limit.
     */
    public function enforceGroupLimit(string $limitPath, int $currentCount, string $message): void
    {
        $limit = $this->groupLimit($limitPath);

        if ($limit !== null && $currentCount >= $limit) {
            $customMessage = $this->groupSetting("messages.limits.{$limitPath}");
            if (is_string($customMessage) && trim($customMessage) !== '') {
                $message = $customMessage;
            }

            abort(429, $message);
        }
    }

    private function mergeSettingsRecursive(array $base, array $add): array
    {
        foreach ($add as $key => $value) {
            if (!array_key_exists($key, $base)) {
                $base[$key] = $value;
                continue;
            }

            $existing = $base[$key];

            if (is_array($existing) && is_array($value)) {
                $base[$key] = $this->mergeSettingsRecursive($existing, $value);
                continue;
            }

            if (is_bool($existing) || is_bool($value)) {
                $base[$key] = (bool) $existing || (bool) $value;
                continue;
            }

            if (is_numeric($existing) && is_numeric($value)) {
                $base[$key] = max((int) $existing, (int) $value);
                continue;
            }

            // Keep first non-empty
            $base[$key] = ($existing !== null && $existing !== '') ? $existing : $value;
        }

        return $base;
    }

    private function decryptSecretAttribute(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $value = trim($value);
        if ($value === '') {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $this->looksLikeEncryptedPayload($value) ? '' : $value;
        }
    }

    private function encryptSecretAttribute(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : Crypt::encryptString($value);
    }

    private function looksLikeEncryptedPayload(string $value): bool
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload)
            && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    /**
     * Get the email lists that belong to the customer.
     */
    public function emailLists(): HasMany
    {
        return $this->hasMany(EmailList::class);
    }

    public function googleIntegrations(): HasMany
    {
        return $this->hasMany(GoogleIntegration::class);
    }

    public function googleIntegrationFor(string $service): ?GoogleIntegration
    {
        $service = strtolower(trim($service));
        if ($service === '') {
            return null;
        }

        if ($this->relationLoaded('googleIntegrations')) {
            return $this->googleIntegrations->firstWhere('service', $service);
        }

        return $this->googleIntegrations()->where('service', $service)->first();
    }

    /**
     * Get the campaigns that belong to the customer.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the auto responders that belong to the customer.
     */
    public function autoResponders(): HasMany
    {
        return $this->hasMany(AutoResponder::class);
    }

    public function automations(): HasMany
    {
        return $this->hasMany(Automation::class);
    }

    /**
     * Get the tracking domains that belong to the customer.
     */
    public function trackingDomains(): HasMany
    {
        return $this->hasMany(TrackingDomain::class);
    }

    public function bounceServers(): HasMany
    {
        return $this->hasMany(BounceServer::class);
    }

    public function replyServers(): HasMany
    {
        return $this->hasMany(ReplyServer::class);
    }

    /**
     * Get the sending domains that belong to the customer.
     */
    public function sendingDomains(): HasMany
    {
        return $this->hasMany(SendingDomain::class);
    }
    public function deliveryServers(): HasMany
    {
        return $this->hasMany(DeliveryServer::class);
    }

    /**
     * Get the transactional emails that belong to the customer.
     */
    public function transactionalEmails(): HasMany
    {
        return $this->hasMany(TransactionalEmail::class);
    }

    /**
     * Get the subscriptions that belong to the customer.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the active subscription for the customer.
     */
    public function activeSubscription(): HasMany
    {
        return $this->hasMany(Subscription::class)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }
}
