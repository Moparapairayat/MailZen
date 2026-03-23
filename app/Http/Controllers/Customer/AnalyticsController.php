<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BounceLog;
use App\Models\CampaignRecipient;
use App\Models\CampaignTracking;
use App\Models\Complaint;
use App\Models\Campaign;
use App\Models\ListSubscriber;
use App\Models\SendingDomain;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request)
    {
        $customer = auth('customer')->user();

        $range = (int) $request->query('range', 7);
        if (!in_array($range, [7, 30], true)) {
            $range = 7;
        }

        $domainId = $request->query('domain');
        $domainId = is_numeric($domainId) ? (int) $domainId : null;

        $end = Carbon::today($customer?->timezone ?: config('app.timezone'))->endOfDay();
        $start = (clone $end)->subDays($range - 1)->startOfDay();

        $prevEnd = (clone $start)->subDay()->endOfDay();
        $prevStart = (clone $prevEnd)->subDays($range - 1)->startOfDay();

        $domains = SendingDomain::query()
            ->where('customer_id', $customer->id)
            ->orderBy('domain')
            ->get(['id', 'domain']);

        $campaignScope = function ($q) use ($customer, $domainId) {
            $q->where('customer_id', $customer->id);
            if ($domainId) {
                $q->where('sending_domain_id', $domainId);
            }
        };

        $recipientBase = CampaignRecipient::query()
            ->whereHas('campaign', $campaignScope)
            ->whereNotNull('sent_at')
            ->whereBetween('sent_at', [$start, $end]);

        $recipientBasePrev = CampaignRecipient::query()
            ->whereHas('campaign', $campaignScope)
            ->whereNotNull('sent_at')
            ->whereBetween('sent_at', [$prevStart, $prevEnd]);

        $total = (clone $recipientBase)->count();
        $delivered = (clone $recipientBase)->whereIn('status', ['sent', 'opened', 'clicked'])->count();
        $bounced = (clone $recipientBase)->where('status', 'bounced')->count();

        $totalPrev = (clone $recipientBasePrev)->count();
        $deliveredPrev = (clone $recipientBasePrev)->whereIn('status', ['sent', 'opened', 'clicked'])->count();
        $bouncedPrev = (clone $recipientBasePrev)->where('status', 'bounced')->count();

        $unsubscribed = ListSubscriber::query()
            ->whereHas('list', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->whereNotNull('unsubscribed_at')
            ->whereBetween('unsubscribed_at', [$start, $end])
            ->count();

        $unsubscribedPrev = ListSubscriber::query()
            ->whereHas('list', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->whereNotNull('unsubscribed_at')
            ->whereBetween('unsubscribed_at', [$prevStart, $prevEnd])
            ->count();

        $hardBounced = BounceLog::query()
            ->whereHas('campaign', $campaignScope)
            ->where('bounce_type', 'hard')
            ->whereBetween('bounced_at', [$start, $end])
            ->count();

        $hardBouncedPrev = BounceLog::query()
            ->whereHas('campaign', $campaignScope)
            ->where('bounce_type', 'hard')
            ->whereBetween('bounced_at', [$prevStart, $prevEnd])
            ->count();

        $opened = CampaignTracking::query()
            ->whereHas('campaign', $campaignScope)
            ->where('event_type', 'opened')
            ->whereBetween('event_at', [$start, $end])
            ->count();

        $openedPrev = CampaignTracking::query()
            ->whereHas('campaign', $campaignScope)
            ->where('event_type', 'opened')
            ->whereBetween('event_at', [$prevStart, $prevEnd])
            ->count();

        $clicked = CampaignTracking::query()
            ->whereHas('campaign', $campaignScope)
            ->where('event_type', 'clicked')
            ->whereBetween('event_at', [$start, $end])
            ->count();

        $clickedPrev = CampaignTracking::query()
            ->whereHas('campaign', $campaignScope)
            ->where('event_type', 'clicked')
            ->whereBetween('event_at', [$prevStart, $prevEnd])
            ->count();

        $complained = Complaint::query()
            ->whereHas('campaign', $campaignScope)
            ->whereBetween('complained_at', [$start, $end])
            ->count();

        $complainedPrev = Complaint::query()
            ->whereHas('campaign', $campaignScope)
            ->whereBetween('complained_at', [$prevStart, $prevEnd])
            ->count();

        $deliveryRate = $total > 0 ? round(($delivered / $total) * 100, 2) : 0;
        $hardBounceRate = $total > 0 ? round(($hardBounced / $total) * 100, 2) : 0;
        $unsubscribeRate = $total > 0 ? round(($unsubscribed / $total) * 100, 2) : 0;
        $spamRate = $total > 0 ? round(($complained / $total) * 100, 2) : 0;

        $bounceRate = $total > 0 ? round(($bounced / $total) * 100, 2) : 0;
        $openRate = $total > 0 ? round(($opened / $total) * 100, 2) : 0;
        $clickRate = $total > 0 ? round(($clicked / $total) * 100, 2) : 0;
        $clickThroughRate = $opened > 0 ? round(($clicked / $opened) * 100, 2) : 0;

        $deliveryRatePrev = $totalPrev > 0 ? round(($deliveredPrev / $totalPrev) * 100, 2) : 0;
        $hardBounceRatePrev = $totalPrev > 0 ? round(($hardBouncedPrev / $totalPrev) * 100, 2) : 0;
        $unsubscribeRatePrev = $totalPrev > 0 ? round(($unsubscribedPrev / $totalPrev) * 100, 2) : 0;
        $spamRatePrev = $totalPrev > 0 ? round(($complainedPrev / $totalPrev) * 100, 2) : 0;
        $openRatePrev = $totalPrev > 0 ? round(($openedPrev / $totalPrev) * 100, 2) : 0;
        $clickRatePrev = $totalPrev > 0 ? round(($clickedPrev / $totalPrev) * 100, 2) : 0;
        $clickThroughRatePrev = $openedPrev > 0 ? round(($clickedPrev / $openedPrev) * 100, 2) : 0;

        $deltas = [
            'sent' => $totalPrev > 0 ? round((($total - $totalPrev) / $totalPrev) * 100, 2) : null,
            'open_rate' => round($openRate - $openRatePrev, 2),
            'click_rate' => round($clickRate - $clickRatePrev, 2),
            'click_through' => round($clickThroughRate - $clickThroughRatePrev, 2),
            'delivery_rate' => round($deliveryRate - $deliveryRatePrev, 2),
            'hard_bounce_rate' => round($hardBounceRate - $hardBounceRatePrev, 2),
            'unsubscribe_rate' => round($unsubscribeRate - $unsubscribeRatePrev, 2),
            'spam_rate' => round($spamRate - $spamRatePrev, 2),
        ];

        $dates = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $series = collect($dates)->mapWithKeys(function ($d) {
            return [$d => [
                'day' => $d,
                'total' => 0,
                'delivered' => 0,
                'bounced' => 0,
                'complained' => 0,
                'clicked' => 0,
                'opened' => 0,
                'unsubscribed' => 0,
                'hard_bounced' => 0,
            ]];
        })->all();

        $recipientByDay = CampaignRecipient::query()
            ->whereHas('campaign', $campaignScope)
            ->whereNotNull('sent_at')
            ->whereBetween('sent_at', [$start, $end])
            ->selectRaw('DATE(sent_at) as day')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status IN ("sent","opened","clicked") THEN 1 ELSE 0 END) as delivered')
            ->selectRaw('SUM(CASE WHEN status = "bounced" THEN 1 ELSE 0 END) as bounced')
            ->groupBy('day')
            ->get();

        foreach ($recipientByDay as $row) {
            $day = (string) $row->day;
            if (!isset($series[$day])) {
                continue;
            }
            $series[$day]['total'] = (int) $row->total;
            $series[$day]['delivered'] = (int) $row->delivered;
            $series[$day]['bounced'] = (int) $row->bounced;
        }

        $opensByDay = CampaignTracking::query()
            ->whereHas('campaign', $campaignScope)
            ->where('event_type', 'opened')
            ->whereBetween('event_at', [$start, $end])
            ->selectRaw('DATE(event_at) as day')
            ->selectRaw('COUNT(*) as opened')
            ->groupBy('day')
            ->get();

        foreach ($opensByDay as $row) {
            $day = (string) $row->day;
            if (!isset($series[$day])) {
                continue;
            }
            $series[$day]['opened'] = (int) $row->opened;
        }

        $clicksByDay = CampaignTracking::query()
            ->whereHas('campaign', $campaignScope)
            ->where('event_type', 'clicked')
            ->whereBetween('event_at', [$start, $end])
            ->selectRaw('DATE(event_at) as day')
            ->selectRaw('COUNT(*) as clicked')
            ->groupBy('day')
            ->get();

        foreach ($clicksByDay as $row) {
            $day = (string) $row->day;
            if (!isset($series[$day])) {
                continue;
            }
            $series[$day]['clicked'] = (int) $row->clicked;
        }

        $complaintsByDay = Complaint::query()
            ->whereHas('campaign', $campaignScope)
            ->whereBetween('complained_at', [$start, $end])
            ->selectRaw('DATE(complained_at) as day')
            ->selectRaw('COUNT(*) as complained')
            ->groupBy('day')
            ->get();

        foreach ($complaintsByDay as $row) {
            $day = (string) $row->day;
            if (!isset($series[$day])) {
                continue;
            }
            $series[$day]['complained'] = (int) $row->complained;
        }

        $unsubscribedByDay = ListSubscriber::query()
            ->whereHas('list', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->whereNotNull('unsubscribed_at')
            ->whereBetween('unsubscribed_at', [$start, $end])
            ->selectRaw('DATE(unsubscribed_at) as day')
            ->selectRaw('COUNT(*) as unsubscribed')
            ->groupBy('day')
            ->get();

        foreach ($unsubscribedByDay as $row) {
            $day = (string) $row->day;
            if (!isset($series[$day])) {
                continue;
            }
            $series[$day]['unsubscribed'] = (int) $row->unsubscribed;
        }

        $hardBouncedByDay = BounceLog::query()
            ->whereHas('campaign', $campaignScope)
            ->where('bounce_type', 'hard')
            ->whereBetween('bounced_at', [$start, $end])
            ->selectRaw('DATE(bounced_at) as day')
            ->selectRaw('COUNT(*) as hard_bounced')
            ->groupBy('day')
            ->get();

        foreach ($hardBouncedByDay as $row) {
            $day = (string) $row->day;
            if (!isset($series[$day])) {
                continue;
            }
            $series[$day]['hard_bounced'] = (int) $row->hard_bounced;
        }

        $maxStack = 0;
        foreach ($series as $row) {
            $stack = (int) ($row['delivered'] + $row['opened'] + $row['clicked'] + $row['bounced'] + $row['complained']);
            $maxStack = max($maxStack, $stack);
        }
        $maxStack = max(1, $maxStack);

        $rateSeries = array_values(array_map(function (array $row) {
            $total = (int) ($row['total'] ?? 0);
            $opened = (int) ($row['opened'] ?? 0);
            $clicked = (int) ($row['clicked'] ?? 0);

            $openRate = $total > 0 ? round(($opened / $total) * 100, 2) : 0;
            $clickRate = $total > 0 ? round(($clicked / $total) * 100, 2) : 0;
            $clickThrough = $opened > 0 ? round(($clicked / $opened) * 100, 2) : 0;

            return [
                'day' => $row['day'],
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
                'click_through' => $clickThrough,
            ];
        }, array_values($series)));

        $deviceCase = 'CASE '
            . 'WHEN user_agent LIKE "%iPad%" OR user_agent LIKE "%Tablet%" THEN "Tablet" '
            . 'WHEN user_agent LIKE "%Watch%" THEN "Smartwatch" '
            . 'WHEN user_agent LIKE "%iPhone%" OR (user_agent LIKE "%Android%" AND user_agent LIKE "%Mobile%") THEN "Smartphone" '
            . 'WHEN user_agent LIKE "%Macintosh%" OR user_agent LIKE "%Windows%" OR user_agent LIKE "%Linux%" THEN "Desktop/Laptop" '
            . 'ELSE "Other" END';

        $deviceRows = CampaignTracking::query()
            ->whereHas('campaign', $campaignScope)
            ->whereIn('event_type', ['opened', 'clicked'])
            ->whereBetween('event_at', [$start, $end])
            ->selectRaw($deviceCase . ' as device')
            ->selectRaw('SUM(CASE WHEN event_type = "opened" THEN 1 ELSE 0 END) as opened')
            ->selectRaw('SUM(CASE WHEN event_type = "clicked" THEN 1 ELSE 0 END) as clicked')
            ->groupBy('device')
            ->get();

        $deviceOrder = ['Smartphone', 'Desktop/Laptop', 'Tablet', 'Smartwatch', 'Other'];
        $deviceStats = collect($deviceOrder)->map(function (string $label) use ($deviceRows) {
            $row = $deviceRows->firstWhere('device', $label);
            return [
                'label' => $label,
                'opened' => (int) ($row->opened ?? 0),
                'clicked' => (int) ($row->clicked ?? 0),
            ];
        })->values()->all();

        $campaignAgg = CampaignRecipient::query()
            ->whereHas('campaign', $campaignScope)
            ->whereNotNull('sent_at')
            ->whereBetween('sent_at', [$start, $end])
            ->selectRaw('campaign_id')
            ->selectRaw('COUNT(*) as sent')
            ->selectRaw('SUM(CASE WHEN status IN ("sent","opened","clicked") THEN 1 ELSE 0 END) as delivered')
            ->selectRaw('SUM(CASE WHEN status = "clicked" THEN 1 ELSE 0 END) as clicked')
            ->selectRaw('SUM(CASE WHEN status IN ("opened","clicked") THEN 1 ELSE 0 END) as opened')
            ->selectRaw('SUM(CASE WHEN status = "bounced" THEN 1 ELSE 0 END) as bounced')
            ->selectRaw('SUM(CASE WHEN status = "unsubscribed" THEN 1 ELSE 0 END) as unsubscribed')
            ->groupBy('campaign_id')
            ->get()
            ->keyBy('campaign_id');

        $campaignIds = $campaignAgg->keys()->filter()->values();

        $complaintsAgg = Complaint::query()
            ->whereHas('campaign', $campaignScope)
            ->whereBetween('complained_at', [$start, $end])
            ->selectRaw('campaign_id')
            ->selectRaw('COUNT(*) as complained')
            ->groupBy('campaign_id')
            ->get()
            ->keyBy('campaign_id');

        $campaignModels = Campaign::query()
            ->where('customer_id', $customer->id)
            ->whereIn('id', $campaignIds)
            ->when($domainId, fn ($q) => $q->where('sending_domain_id', $domainId))
            ->orderByDesc('send_at')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'subject', 'send_at', 'created_at']);

        $campaignRows = $campaignModels->map(function (Campaign $c) use ($campaignAgg, $complaintsAgg) {
            $agg = $campaignAgg->get($c->id);
            $sent = (int) ($agg->sent ?? 0);
            $delivered = (int) ($agg->delivered ?? 0);
            $opened = (int) ($agg->opened ?? 0);
            $clicked = (int) ($agg->clicked ?? 0);
            $unsubscribed = (int) ($agg->unsubscribed ?? 0);
            $complained = (int) ($complaintsAgg->get($c->id)->complained ?? 0);

            $clickRate = $sent > 0 ? round(($clicked / $sent) * 100, 2) : 0;
            $clickThrough = $opened > 0 ? round(($clicked / $opened) * 100, 2) : 0;
            $deliveredRate = $sent > 0 ? round(($delivered / $sent) * 100, 2) : 0;
            $unsubscribeRate = $sent > 0 ? round(($unsubscribed / $sent) * 100, 2) : 0;
            $spamRate = $sent > 0 ? round(($complained / $sent) * 100, 2) : 0;

            return [
                'id' => $c->id,
                'name' => $c->name,
                'published_at' => ($c->send_at ?? $c->created_at),
                'sent' => $sent,
                'click_rate' => $clickRate,
                'click_through' => $clickThrough,
                'delivered_rate' => $deliveredRate,
                'unsubscribe_rate' => $unsubscribeRate,
                'spam_rate' => $spamRate,
            ];
        })->values()->all();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $campaignPage = array_slice($campaignRows, max(0, ($page - 1) * $perPage), $perPage);
        $campaigns = new LengthAwarePaginator(
            $campaignPage,
            count($campaignRows),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('customer.analytics.index', [
            'domains' => $domains,
            'selectedDomainId' => $domainId,
            'range' => $range,
            'summary' => [
                'total' => $total,
                'delivered' => $delivered,
                'delivery_rate' => $deliveryRate,
                'bounced' => $bounced,
                'bounce_rate' => $bounceRate,
                'complained' => $complained,
                'opened' => $opened,
                'open_rate' => $openRate,
                'clicked' => $clicked,
                'click_rate' => $clickRate,
                'click_through' => $clickThroughRate,
                'unsubscribed' => $unsubscribed,
                'unsubscribe_rate' => $unsubscribeRate,
                'hard_bounced' => $hardBounced,
                'hard_bounce_rate' => $hardBounceRate,
                'spam_rate' => $spamRate,
                'deltas' => $deltas,
            ],
            'series' => array_values($series),
            'rateSeries' => $rateSeries,
            'deviceStats' => $deviceStats,
            'campaigns' => $campaigns,
            'maxStack' => $maxStack,
        ]);
    }
}

