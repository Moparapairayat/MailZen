<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerGroup extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'permissions',
        'settings',
        'quota',
        'max_lists',
        'max_subscribers',
        'max_campaigns',
        'is_system',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'settings' => 'array',
            'quota' => 'decimal:2',
            'is_system' => 'boolean',
        ];
    }

    public function limit(string $path, int $default = 0): int
    {
        $value = data_get($this->settings ?? [], $path);

        if (is_numeric($value) && (int) $value > 0) {
            return (int) $value;
        }

        return match ($path) {
            'sending_quota.monthly_quota' => (int) ($this->quota ?? $default),
            'lists.limits.max_lists' => (int) ($this->max_lists ?? $default),
            'lists.limits.max_subscribers' => (int) ($this->max_subscribers ?? $default),
            'campaigns.limits.max_campaigns' => (int) ($this->max_campaigns ?? $default),
            default => $default,
        };
    }

    public function displayAccessAndLimits(): array
    {
        $settings = (array) ($this->settings ?? []);

        $formatLimit = static function (int $value): string {
            return $value > 0 ? number_format($value) : (string) __('Unlimited');
        };

        $formatBool = static function (bool $value): string {
            return $value ? (string) __('Yes') : (string) __('No');
        };

        $rows = [];
        $add = static function (array &$rows, string $label, string $value, bool $status): void {
            $rows[] = ['label' => $label, 'value' => $value, 'status' => $status];
        };

        $monthlyQuota = (int) $this->limit('sending_quota.monthly_quota', 0);
        $add($rows, (string) __('Emails per month'), $formatLimit($monthlyQuota), true);

        $dailyQuota = (int) data_get($settings, 'sending_quota.daily_quota', 0);
        if ($dailyQuota > 0) {
            $add($rows, (string) __('Emails per day'), $formatLimit($dailyQuota), true);
        }

        $weeklyQuota = (int) data_get($settings, 'sending_quota.weekly_quota', 0);
        if ($weeklyQuota > 0) {
            $add($rows, (string) __('Emails per week'), $formatLimit($weeklyQuota), true);
        }

        $add($rows, (string) __('Max lists'), $formatLimit((int) $this->limit('lists.limits.max_lists', 0)), true);
        $add($rows, (string) __('Max subscribers'), $formatLimit((int) $this->limit('lists.limits.max_subscribers', 0)), true);

        $maxSubscribersPerList = (int) data_get($settings, 'lists.limits.max_subscribers_per_list', 0);
        if ($maxSubscribersPerList > 0) {
            $add($rows, (string) __('Max subscribers per list'), $formatLimit($maxSubscribersPerList), true);
        }

        $maxFormsPerList = (int) data_get($settings, 'lists.limits.max_forms_per_list', 0);
        if ($maxFormsPerList > 0) {
            $add($rows, (string) __('Max forms per list'), $formatLimit($maxFormsPerList), true);
        }

        $add($rows, (string) __('Max campaigns'), $formatLimit((int) $this->limit('campaigns.limits.max_campaigns', 0)), true);

        $maxActiveCampaigns = (int) data_get($settings, 'campaigns.limits.max_active_campaigns', 0);
        if ($maxActiveCampaigns > 0) {
            $add($rows, (string) __('Max active campaigns'), $formatLimit($maxActiveCampaigns), true);
        }

        $abTesting = (bool) data_get($settings, 'campaigns.features.ab_testing', false);
        $add($rows, (string) __('A/B testing'), $formatBool($abTesting), $abTesting);

        $automations = (bool) data_get($settings, 'automations.enabled', false);
        $add($rows, (string) __('Automations'), $formatBool($automations), $automations);

        $autoResponders = (bool) data_get($settings, 'autoresponders.enabled', false);
        $add($rows, (string) __('Auto responders'), $formatBool($autoResponders), $autoResponders);
        $maxAutoresponders = (int) data_get($settings, 'autoresponders.max_autoresponders', 0);
        if ($maxAutoresponders > 0) {
            $add($rows, (string) __('Max auto responders'), $formatLimit($maxAutoresponders), true);
        }

        $emailValidationAccess = (bool) data_get($settings, 'email_validation.access', false);
        $add($rows, (string) __('Email validation access'), $formatBool($emailValidationAccess), $emailValidationAccess);

        $emailValidationMonthlyLimit = (int) data_get($settings, 'email_validation.monthly_limit', 0);
        if ($emailValidationMonthlyLimit > 0) {
            $add($rows, (string) __('Email validation monthly limit'), $formatLimit($emailValidationMonthlyLimit), true);
        }

        $googleIntegrations = (bool) data_get($settings, 'integrations.permissions.can_access_google', false);
        $add($rows, (string) __('Google integrations'), $formatBool($googleIntegrations), $googleIntegrations);

        $aiMustUseOwnKeys = (bool) data_get($settings, 'ai.must_use_own_keys', false);
        $add($rows, (string) __('AI must use own keys'), $formatBool($aiMustUseOwnKeys), $aiMustUseOwnKeys);
        $aiTokenLimit = (int) data_get($settings, 'ai.token_limit', 0);
        if ($aiTokenLimit > 0) {
            $add($rows, (string) __('AI token limit'), $formatLimit($aiTokenLimit), true);
        }
        $aiImageCredits = (int) data_get($settings, 'ai.image_credits', 0);
        if ($aiImageCredits > 0) {
            $add($rows, (string) __('AI image credits'), $formatLimit($aiImageCredits), true);
        }

        $trackingDomainsCanManage = (bool) data_get($settings, 'domains.tracking_domains.can_manage', false) || $this->hasPermission('domains.tracking_domains.can_manage');
        $sendingDomainsCanManage = (bool) data_get($settings, 'domains.sending_domains.can_manage', false) || $this->hasPermission('domains.sending_domains.can_manage');
        $add($rows, (string) __('Can manage tracking domains'), $formatBool($trackingDomainsCanManage), $trackingDomainsCanManage);
        $add($rows, (string) __('Can manage sending domains'), $formatBool($sendingDomainsCanManage), $sendingDomainsCanManage);

        $maxTrackingDomains = (int) data_get($settings, 'domains.tracking_domains.max_tracking_domains', 0);
        if ($maxTrackingDomains > 0) {
            $add($rows, (string) __('Max tracking domains'), $formatLimit($maxTrackingDomains), true);
        }

        $maxSendingDomains = (int) data_get($settings, 'domains.sending_domains.max_sending_domains', 0);
        if ($maxSendingDomains > 0) {
            $add($rows, (string) __('Max sending domains'), $formatLimit($maxSendingDomains), true);
        }

        $canAddDeliveryServers = (bool) data_get($settings, 'servers.permissions.can_add_delivery_servers', false) || $this->hasPermission('servers.permissions.can_add_delivery_servers');
        $add($rows, (string) __('Can add delivery servers'), $formatBool($canAddDeliveryServers), $canAddDeliveryServers);

        $maxDeliveryServers = (int) data_get($settings, 'servers.limits.max_delivery_servers', 0);
        if ($maxDeliveryServers > 0) {
            $add($rows, (string) __('Max delivery servers'), $formatLimit($maxDeliveryServers), true);
        }

        $maxBounceServers = (int) data_get($settings, 'servers.limits.max_bounce_servers', 0);
        if ($maxBounceServers > 0) {
            $add($rows, (string) __('Max bounce servers'), $formatLimit($maxBounceServers), true);
        }

        $maxEmailBoxMonitors = (int) data_get($settings, 'servers.limits.max_email_box_monitors', 0);
        if ($maxEmailBoxMonitors > 0) {
            $add($rows, (string) __('Max email box monitors'), $formatLimit($maxEmailBoxMonitors), true);
        }

        $allowed = [];
        $disallowed = [];

        foreach ($rows as $row) {
            if (($row['status'] ?? true) === false) {
                $disallowed[] = $row;
            } else {
                $allowed[] = $row;
            }
        }

        return array_merge($allowed, $disallowed);
    }

    /**
     * Get the customers that belong to the customer group.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_customer_group')
            ->withTimestamps();
    }

    public function allocatedDeliveryServers(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryServer::class, 'customer_group_delivery_server')
            ->withTimestamps();
    }

    /**
     * Check if group has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Add a permission to the group.
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Remove a permission from the group.
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_values(array_filter($permissions, fn($p) => $p !== $permission));
        $this->permissions = $permissions;
        $this->save();
    }
}

