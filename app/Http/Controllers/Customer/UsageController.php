<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Billing\UsageService;
use App\Models\BounceServer;
use App\Models\Campaign;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\SendingDomain;
use App\Models\TrackingDomain;
use Illuminate\Http\Request;

class UsageController extends Controller
{
    public function __construct(
        private readonly UsageService $usageService,
    ) {
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $subscription = $customer->subscriptions()->latest()->first();

        $usage = $this->usageService->getUsage($customer);

        $metrics = [
            'emails_sent_this_month',
            'email_validation_emails_this_month',
            'lists_count',
            'subscribers_count',
            'campaigns_count',
            'bounce_servers_count',
            'tracking_domains_count',
            'sending_domains_count',
            'ai_tokens_used',
        ];

        $usage['lists_count'] = EmailList::query()
            ->where('customer_id', $customer->id)
            ->count();

        $usage['subscribers_count'] = ListSubscriber::query()
            ->whereHas('list', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->where('status', 'confirmed')
            ->count();

        $usage['campaigns_count'] = Campaign::query()
            ->where('customer_id', $customer->id)
            ->count();

        $bounceServersAccess = (
            $customer->groupAllows('servers.permissions.can_add_bounce_servers')
            || $customer->groupAllows('servers.permissions.must_add_bounce_server')
        );

        $usage['bounce_servers_count'] = BounceServer::query()
            ->where('customer_id', $customer->id)
            ->count();

        $usage['tracking_domains_count'] = TrackingDomain::query()
            ->where('customer_id', $customer->id)
            ->count();

        $usage['sending_domains_count'] = SendingDomain::query()
            ->where('customer_id', $customer->id)
            ->count();

        $usage['ai_tokens_used'] = (int) ($customer->ai_token_usage ?? 0);

        $metricAccess = [
            'emails_sent_this_month' => true,
            'email_validation_emails_this_month' => $customer->groupAllows('email_validation.access'),
            'lists_count' => true,
            'subscribers_count' => true,
            'campaigns_count' => true,
            'bounce_servers_count' => $bounceServersAccess,
            'tracking_domains_count' => (
                $customer->groupAllows('domains.tracking_domains.permissions.can_access_tracking_domains')
                || $customer->groupAllows('domains.tracking_domains.can_manage')
            ),
            'sending_domains_count' => (
                $customer->groupAllows('domains.sending_domains.permissions.can_access_sending_domains')
                || $customer->groupAllows('domains.sending_domains.can_manage')
            ),
            'ai_tokens_used' => $customer->groupAllows('ai_tools.permissions.can_access_ai_tools'),
        ];

        $limits = [
            'emails_sent_this_month' => $customer->groupLimit('sending_quota.monthly_quota'),
            'email_validation_emails_this_month' => null,
            'lists_count' => $customer->groupLimit('lists.limits.max_lists'),
            'subscribers_count' => $customer->groupLimit('lists.limits.max_subscribers'),
            'campaigns_count' => $customer->groupLimit('campaigns.limits.max_campaigns'),
            'bounce_servers_count' => $customer->groupLimit('servers.limits.max_bounce_servers'),
            'tracking_domains_count' => $customer->groupLimit('domains.tracking_domains.max_tracking_domains'),
            'sending_domains_count' => $customer->groupLimit('domains.sending_domains.max_sending_domains'),
            'ai_tokens_used' => null,
        ];

        $aiTokenLimit = (int) $customer->groupSetting('ai.token_limit', 0);
        $limits['ai_tokens_used'] = $aiTokenLimit > 0 ? $aiTokenLimit : null;

        $emailValidationLimit = (int) $customer->groupSetting('email_validation.monthly_limit', 0);
        $limits['email_validation_emails_this_month'] = $emailValidationLimit > 0 ? $emailValidationLimit : null;

        if ($limits['emails_sent_this_month'] === null) {
            $fallbackQuota = (int) ($customer->quota ?? 0);
            $limits['emails_sent_this_month'] = $fallbackQuota > 0 ? $fallbackQuota : null;
        }

        if ($limits['lists_count'] === null) {
            $fallback = (int) ($customer->max_lists ?? 0);
            $limits['lists_count'] = $fallback > 0 ? $fallback : null;
        }

        if ($limits['subscribers_count'] === null) {
            $fallback = (int) ($customer->max_subscribers ?? 0);
            $limits['subscribers_count'] = $fallback > 0 ? $fallback : null;
        }

        if ($limits['campaigns_count'] === null) {
            $fallback = (int) ($customer->max_campaigns ?? 0);
            $limits['campaigns_count'] = $fallback > 0 ? $fallback : null;
        }

        if ($limits['bounce_servers_count'] !== null && (int) $limits['bounce_servers_count'] <= 0) {
            $limits['bounce_servers_count'] = null;
        }

        if ($limits['tracking_domains_count'] !== null && (int) $limits['tracking_domains_count'] <= 0) {
            $limits['tracking_domains_count'] = null;
        }

        if ($limits['sending_domains_count'] !== null && (int) $limits['sending_domains_count'] <= 0) {
            $limits['sending_domains_count'] = null;
        }

        $usageWithLimits = collect($metrics)
            ->map(function ($metric) use ($usage, $limits, $metricAccess) {
                $current = (int) ($usage[$metric] ?? 0);
                $limit = $limits[$metric] ?? null;
                $hasAccess = (bool) ($metricAccess[$metric] ?? true);
                $percentage = ($hasAccess && $limit) ? ($current / $limit) * 100 : null;

                return [
                    'metric' => $metric,
                    'current' => $current,
                    'limit' => $limit,
                    'percentage' => $percentage,
                    'has_access' => $hasAccess,
                ];
            })
            ->filter(fn ($row) => (bool) ($row['has_access'] ?? true))
            ->values();

        $accessBoxes = [
            [
                'key' => 'servers.permissions.can_add_delivery_servers',
                'label' => 'Delivery Servers',
                'has_access' => (
                    $customer->groupAllows('servers.permissions.can_use_system_servers')
                    || $customer->groupAllows('servers.permissions.can_add_delivery_servers')
                    || $customer->groupAllows('servers.permissions.can_select_delivery_servers_for_campaigns')
                ),
            ],
            [
                'key' => 'servers.permissions.can_add_bounce_servers',
                'label' => 'Bounce Servers',
                'has_access' => $bounceServersAccess,
            ],
            [
                'key' => 'domains.tracking_domains.can_manage',
                'label' => 'Tracking Domains',
                'has_access' => $customer->groupAllows('domains.tracking_domains.can_manage'),
            ],
            [
                'key' => 'domains.sending_domains.can_manage',
                'label' => 'Sending Domains',
                'has_access' => $customer->groupAllows('domains.sending_domains.can_manage'),
            ],
            [
                'key' => 'email_validation.access',
                'label' => 'Email Validation',
                'has_access' => $customer->groupAllows('email_validation.access'),
            ],
            [
                'key' => 'autoresponders.enabled',
                'label' => 'Auto Responders',
                'has_access' => $customer->groupAllows('autoresponders.enabled'),
            ],
            [
                'key' => 'campaigns.features.ab_testing',
                'label' => 'A/B Testing',
                'has_access' => $customer->groupAllows('campaigns.features.ab_testing'),
            ],
        ];

        return view('customer.usage.index', compact('subscription', 'usageWithLimits', 'accessBoxes'));
    }
}
