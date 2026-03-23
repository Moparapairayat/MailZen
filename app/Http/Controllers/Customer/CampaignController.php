<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\StartCampaignJob;
use App\Models\BounceServer;
use App\Models\ReplyServer;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\DeliveryServer;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\SendingDomain;
use App\Models\TrackingDomain;
use App\Models\Template;
use App\Services\CampaignService;
use App\Services\DeliveryServerService;
use App\Services\SpamScoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\CampaignStatusUpdatedNotification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignService $campaignService
    ) {
        $this->middleware('customer.access:campaigns.permissions.can_access_campaigns')->only([
            'index',
            'show',
            'stats',
            'recipients',
            'replies',
        ]);
        $this->middleware('customer.access:campaigns.permissions.can_create_campaigns')->only(['create', 'store', 'duplicate']);
        $this->middleware('customer.access:campaigns.permissions.can_edit_campaigns')->only(['edit', 'update']);
        $this->middleware('customer.access:campaigns.permissions.can_delete_campaigns')->only(['destroy']);
        $this->middleware('customer.access:campaigns.permissions.can_start_campaigns')->only(['start', 'pause', 'resume', 'rerun']);

        $this->middleware('demo.prevent')->only([
            'create',
            'store',
            'duplicate',
            'destroy',
            'start',
            'pause',
            'resume',
            'rerun',
        ]);
    }

    protected function authorizeOwnership(Campaign $campaign): Campaign
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $campaign->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $campaign;
    }

    private function normalizeUnlayerDesign(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || $value === 'null') {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function buildUnlayerTemplateData(mixed $value): ?array
    {
        $decoded = $this->normalizeUnlayerDesign($value);
        if ($decoded === null) {
            return null;
        }

        return [
            'builder' => 'unlayer',
            'unlayer' => $decoded,
        ];
    }

    private function unlayerDesignFromCampaign(Campaign $campaign): ?array
    {
        $data = $campaign->template_data;
        if (!is_array($data)) {
            return null;
        }

        if (($data['builder'] ?? null) !== 'unlayer') {
            return null;
        }

        $unlayer = $data['unlayer'] ?? null;
        return is_array($unlayer) ? $unlayer : null;
    }

    private function buildCampaignTagsByList($emailLists): array
    {
        $tagsByList = [];
        $listIds = [];

        foreach ($emailLists as $list) {
            $listId = (int) ($list->id ?? 0);
            if ($listId <= 0) {
                continue;
            }

            $listIds[] = $listId;

            $defs = is_array($list->custom_fields) ? $list->custom_fields : [];
            $custom = [];
            foreach ($defs as $def) {
                if (!is_array($def)) {
                    continue;
                }

                $key = trim((string) ($def['key'] ?? ''));
                if ($key === '') {
                    continue;
                }

                $label = trim((string) ($def['label'] ?? $key));
                $custom[] = [
                    'label' => $label,
                    'tag' => '@{{cf:' . $key . '}}',
                ];
            }

            $tagsByList[(string) $listId] = [
                'custom' => $custom,
                'standard' => [],
            ];
        }

        if (empty($listIds)) {
            return $tagsByList;
        }

        $availabilityRows = ListSubscriber::query()
            ->select('list_id')
            ->selectRaw("MAX(CASE WHEN TRIM(COALESCE(first_name, '')) <> '' THEN 1 ELSE 0 END) as has_first_name")
            ->selectRaw("MAX(CASE WHEN TRIM(COALESCE(last_name, '')) <> '' THEN 1 ELSE 0 END) as has_last_name")
            ->whereIn('list_id', $listIds)
            ->whereNull('deleted_at')
            ->groupBy('list_id')
            ->get()
            ->keyBy(function ($row) {
                return (string) $row->list_id;
            });

        foreach ($listIds as $listId) {
            $key = (string) $listId;
            $row = $availabilityRows->get($key);
            $hasFirst = (bool) ($row?->has_first_name ?? false);
            $hasLast = (bool) ($row?->has_last_name ?? false);
            $hasName = $hasFirst || $hasLast;

            $standard = [];
            if ($hasFirst) {
                $standard[] = ['label' => 'First Name', 'tag' => '@{{first_name}}'];
            }
            if ($hasLast) {
                $standard[] = ['label' => 'Last Name', 'tag' => '@{{last_name}}'];
            }

            $standard[] = ['label' => 'Email', 'tag' => '@{{email}}'];

            if ($hasName) {
                $standard[] = ['label' => 'Full Name', 'tag' => '@{{full_name}}'];
                $standard[] = ['label' => 'Name', 'tag' => '@{{name}}'];
            }

            $standard[] = ['label' => 'Unsubscribe URL', 'tag' => '{unsubscribe_url}'];

            if (isset($tagsByList[$key])) {
                $tagsByList[$key]['standard'] = $standard;
            }
        }

        return $tagsByList;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'type']);
        $campaigns = $this->campaignService->getPaginated(auth('customer')->user(), $filters);

        // Calculate expected recipients for campaigns that haven't started yet
        $campaigns->getCollection()->transform(function ($campaign) {
            if ($campaign->total_recipients === 0 && $campaign->emailList) {
                $campaign->expected_recipients = $this->calculateExpectedRecipients($campaign);
            } else {
                $campaign->expected_recipients = $campaign->total_recipients;
            }
            return $campaign;
        });

        return view('customer.campaigns.index', compact('campaigns', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customer = auth('customer')->user();
        $runPreflightIssues = [];
        $emailLists = EmailList::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->get();

        $templates = Template::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->orWhere(function ($subQ) {
                  $subQ->where('is_public', true)
                       ->where('is_system', false);
              });
        })
        ->whereIn('type', ['email', 'campaign'])
        ->get();

        $footerTemplates = Template::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->orWhere(function ($subQ) {
                  $subQ->where('is_public', true)
                       ->where('is_system', false);
              });
        })
        ->where('type', 'footer')
        ->get();

        $signatureTemplates = Template::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->orWhere(function ($subQ) {
                  $subQ->where('is_public', true)
                       ->where('is_system', false);
              });
        })
        ->where('type', 'signature')
        ->get();

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $mustAddReply = (bool) $customer->groupSetting('servers.permissions.must_add_reply_server', false);
        $mustAddSending = (bool) $customer->groupSetting('domains.sending_domains.must_add', false);
        $mustAddTracking = (bool) $customer->groupSetting('domains.tracking_domains.must_add', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $deliveryServers = app(DeliveryServerService::class)->getSelectableDeliveryServersForCustomer(
            $customer,
            $mustAddDelivery,
            $canUseSystem
        );

        $replyServers = ReplyServer::query()
            ->where('active', true)
            ->when($mustAddReply, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->orderBy('name')
            ->get();

        $sendingDomains = SendingDomain::query()
            ->where('status', 'verified')
            ->when($mustAddSending, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->orderBy('domain')
            ->get();

        $trackingDomains = TrackingDomain::query()
            ->where('status', 'verified')
            ->when($mustAddTracking, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->orderBy('domain')
            ->get();

        if ((bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false)) {
            $hasSelectableDelivery = app(DeliveryServerService::class)
                ->querySelectableDeliveryServersForCustomer($customer, $mustAddDelivery, $canUseSystem)
                ->exists();

            if (!$hasSelectableDelivery) {
                $runPreflightIssues[] = 'You must add a delivery server before running a campaign.';
            }
        }

        if ((bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false)) {
            $hasOwnBounce = BounceServer::query()
                ->where('customer_id', $customer->id)
                ->where('active', true)
                ->exists();

            if (!$hasOwnBounce) {
                $runPreflightIssues[] = 'You must add a bounce server before running a campaign.';
            }
        }

        if ($mustAddReply) {
            $hasOwnReply = ReplyServer::query()
                ->where('customer_id', $customer->id)
                ->where('active', true)
                ->exists();

            if (!$hasOwnReply) {
                $runPreflightIssues[] = 'You must add a reply server before running a campaign.';
            }
        }

        if ((bool) $customer->groupSetting('domains.sending_domains.must_add', false)) {
            $hasOwnSending = SendingDomain::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'verified')
                ->exists();

            if (!$hasOwnSending) {
                $runPreflightIssues[] = 'You must add and verify a sending domain before running a campaign.';
            }
        }

        if ((bool) $customer->groupSetting('domains.tracking_domains.must_add', false)) {
            $hasOwnTracking = TrackingDomain::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'verified')
                ->exists();

            if (!$hasOwnTracking) {
                $runPreflightIssues[] = 'You must add and verify a tracking domain before running a campaign.';
            }
        }

        $bounceServers = BounceServer::query()
            ->where('active', true)
            ->when((bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false), function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->orderBy('name')
            ->get();

        $unlayerProjectId = config('services.unlayer.project_id');
        $unlayerDesign = null;
        $campaignTagsByList = $this->buildCampaignTagsByList($emailLists);

        return view('customer.campaigns.create', compact('emailLists', 'templates', 'footerTemplates', 'signatureTemplates', 'deliveryServers', 'replyServers', 'sendingDomains', 'trackingDomains', 'bounceServers', 'runPreflightIssues', 'unlayerProjectId', 'unlayerDesign', 'campaignTagsByList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customer = auth('customer')->user();
        $customer->enforceGroupLimit('campaigns.limits.max_campaigns', $customer->campaigns()->count(), 'Campaign limit reached.');

        $mustAddReply = (bool) $customer->groupSetting('servers.permissions.must_add_reply_server', false);
        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $selectableDeliveryServerIds = app(DeliveryServerService::class)
            ->getSelectableDeliveryServerIdsForCustomer($customer, $mustAddDelivery, $canUseSystem);

        $payload = $request->all();
        if (array_key_exists('delivery_server_id', $payload) && $payload['delivery_server_id'] === '') {
            $payload['delivery_server_id'] = null;
        }

        $validated = validator($payload, [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'list_id' => ['nullable', 'exists:email_lists,id'],
            'delivery_server_id' => [
                $mustAddDelivery ? 'required' : 'nullable',
                'integer',
                Rule::in($selectableDeliveryServerIds),
            ],
            'reply_server_id' => [
                'nullable',
                Rule::exists('reply_servers', 'id')->where(function ($q) use ($customer, $mustAddReply, $canUseSystem) {
                    $q->where('active', true);

                    if ($mustAddReply || !$canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        return;
                    }

                    $q->where(function ($sub) use ($customer) {
                        $sub->where('customer_id', $customer->id)
                            ->orWhereNull('customer_id');
                    });
                }),
            ],
            'sending_domain_id' => ['nullable', 'exists:sending_domains,id'],
            'tracking_domain_id' => ['nullable', 'exists:tracking_domains,id'],
            'template_id' => [
                'nullable',
                Rule::exists('templates', 'id')->where(function ($q) use ($customer) {
                    $q->whereIn('type', ['email', 'campaign'])
                        ->whereNull('deleted_at')
                        ->where(function ($subQ) use ($customer) {
                            $subQ->where('customer_id', $customer->id)
                                ->orWhere(function ($inner) {
                                    $inner->where('is_public', true)->where('is_system', false);
                                });
                        });
                }),
            ],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'type' => ['nullable', 'in:regular,autoresponder,recurring'],
            'status' => ['nullable', 'in:draft,queued,scheduled,running,paused,completed,failed'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'template_data' => ['nullable'],
            'footer_template_id' => [
                'nullable',
                Rule::exists('templates', 'id')->where(function ($q) use ($customer) {
                    $q->where('type', 'footer')
                        ->whereNull('deleted_at')
                        ->where(function ($subQ) use ($customer) {
                            $subQ->where('customer_id', $customer->id)
                                ->orWhere(function ($inner) {
                                    $inner->where('is_public', true)->where('is_system', false);
                                });
                        });
                }),
            ],
            'signature_template_id' => [
                'nullable',
                Rule::exists('templates', 'id')->where(function ($q) use ($customer) {
                    $q->where('type', 'signature')
                        ->whereNull('deleted_at')
                        ->where(function ($subQ) use ($customer) {
                            $subQ->where('customer_id', $customer->id)
                                ->orWhere(function ($inner) {
                                    $inner->where('is_public', true)->where('is_system', false);
                                });
                        });
                }),
            ],
            'send_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date'],
            'recurring_interval_days' => ['nullable', 'integer', 'min:1'],
            'track_opens' => ['nullable', 'boolean'],
            'track_clicks' => ['nullable', 'boolean'],
            'enable_spintax' => ['nullable', 'boolean'],
            'spam_scoring_enabled' => ['nullable', 'boolean'],
        ])->validate();

        $unlayerData = $this->buildUnlayerTemplateData($request->input('template_data'));
        if ($unlayerData !== null) {
            $validated['template_data'] = $unlayerData;
        } else {
            unset($validated['template_data']);
        }

        if (empty($validated['plain_text_content']) && !empty($validated['html_content'])) {
            $validated['plain_text_content'] = trim(preg_replace('/\s+/', ' ', strip_tags($validated['html_content'])));
        }

        $customerTimezone = $customer->timezone ?? 'UTC';
        if (!empty($validated['send_at'])) {
            $validated['send_at'] = Carbon::parse($validated['send_at'], $customerTimezone)->utc();
        }
        if (!empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = Carbon::parse($validated['scheduled_at'], $customerTimezone)->utc();
        }

        // Convert empty string to null for nullable fields
        if (isset($validated['delivery_server_id']) && $validated['delivery_server_id'] === '') {
            $validated['delivery_server_id'] = null;
        }
        if (isset($validated['reply_server_id']) && $validated['reply_server_id'] === '') {
            $validated['reply_server_id'] = null;
        }
        if (isset($validated['bounce_server_id']) && $validated['bounce_server_id'] === '') {
            $validated['bounce_server_id'] = null;
        }
        if (isset($validated['sending_domain_id']) && $validated['sending_domain_id'] === '') {
            $validated['sending_domain_id'] = null;
        }
        if (isset($validated['tracking_domain_id']) && $validated['tracking_domain_id'] === '') {
            $validated['tracking_domain_id'] = null;
        }
        if (isset($validated['list_id']) && $validated['list_id'] === '') {
            $validated['list_id'] = null;
        }
        if (isset($validated['template_id']) && $validated['template_id'] === '') {
            $validated['template_id'] = null;
        }

        $footerTemplateId = $validated['footer_template_id'] ?? null;
        $signatureTemplateId = $validated['signature_template_id'] ?? null;
        unset($validated['footer_template_id'], $validated['signature_template_id']);

        $settings = (array) ($validated['settings'] ?? []);
        if (!empty($footerTemplateId)) {
            $settings['footer_template_id'] = (int) $footerTemplateId;
        }
        if (!empty($signatureTemplateId)) {
            $settings['signature_template_id'] = (int) $signatureTemplateId;
        }
        
        // Save spintax and spam scoring settings
        if (isset($validated['enable_spintax'])) {
            $settings['enable_spintax'] = (bool) $validated['enable_spintax'];
        }
        if (isset($validated['spam_scoring_enabled'])) {
            $settings['spam_scoring_enabled'] = (bool) $validated['spam_scoring_enabled'];
        }
        
        if (!empty($settings)) {
            $validated['settings'] = $settings;
        }

        // Remove from validated as they're now in settings
        unset($validated['enable_spintax'], $validated['spam_scoring_enabled']);

        if (!empty($validated['send_at']) && empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = $validated['send_at'];
            $validated['status'] = 'scheduled';
        }

        if (($validated['type'] ?? 'regular') === 'recurring') {
            $settings = (array) ($validated['settings'] ?? []);
            $settings['recurring'] = array_merge((array) ($settings['recurring'] ?? []), [
                'interval_days' => (int) ($validated['recurring_interval_days'] ?? 7),
            ]);
            $validated['settings'] = $settings;

            if (empty($validated['scheduled_at'])) {
                $validated['scheduled_at'] = now();
            }
            $validated['status'] = 'scheduled';
        }

        unset($validated['recurring_interval_days']);

        $campaign = $this->campaignService->create($customer, $validated);

        return redirect()
            ->route('customer.campaigns.show', $campaign)
            ->with('success', 'Campaign created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        $this->authorizeOwnership($campaign);
        $runPreflightIssues = [];

        if ($campaign->canStart()) {
            try {
                $this->campaignService->ensureCanRun($campaign);
            } catch (\RuntimeException $e) {
                $runPreflightIssues[] = $e->getMessage();
            }
        }
        // Sync stats from actual recipient statuses to ensure accuracy
        $campaign->syncStats();
        
        $campaign->load(['emailList', 'trackingDomain', 'sendingDomain', 'deliveryServer', 'variants', 'recipients', 'logs']);
        
        // Calculate total recipients - use actual count if campaign has started, otherwise calculate expected
        $totalRecipients = $campaign->total_recipients;
        if ($campaign->total_recipients === 0 && $campaign->emailList) {
            // Calculate expected recipients for campaigns that haven't started yet
            $totalRecipients = $this->calculateExpectedRecipients($campaign);
        }

        // Calculate initial stats
        $delivered = max(0, $campaign->sent_count - $campaign->bounced_count);
        
        // Recipient status breakdown
        $recipientStatuses = $campaign->recipients()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Calculate unique opens (recipients who opened at least once)
        $uniqueOpens = ($recipientStatuses['opened'] ?? 0) + ($recipientStatuses['clicked'] ?? 0);
        
        // Calculate rates based on unique opens
        $openRate = $delivered > 0 ? round(($uniqueOpens / $delivered) * 100, 2) : 0;
        $clickRate = $delivered > 0 ? round(($campaign->clicked_count / $delivered) * 100, 2) : 0;
        $bounceRate = $campaign->sent_count > 0 ? round(($campaign->bounced_count / $campaign->sent_count) * 100, 2) : 0;
        $failureRate = $campaign->sent_count > 0 ? round(($campaign->failed_count / $campaign->sent_count) * 100, 2) : 0;
        $deliveryRate = $totalRecipients > 0 
            ? round(($delivered / $totalRecipients) * 100, 2)
            : 0;

        // Top clicked links
        $topLinks = $campaign->logs()
            ->where('event', 'clicked')
            ->whereNotNull('url')
            ->selectRaw('url, COUNT(*) as clicks')
            ->groupBy('url')
            ->orderByDesc('clicks')
            ->limit(10)
            ->get();

        // Error breakdown
        $errorBreakdown = $campaign->recipients()
            ->where('status', 'failed')
            ->whereNotNull('failure_reason')
            ->selectRaw('failure_reason, COUNT(*) as count')
            ->groupBy('failure_reason')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Calculate sending speed
        $sendingSpeed = 0;
        if ($campaign->started_at && $campaign->sent_count > 0) {
            $secondsElapsed = max(1, now()->diffInSeconds($campaign->started_at));
            $sendingSpeed = round($campaign->sent_count / $secondsElapsed, 2);
        }

        // Check deliverability (DKIM/SPF/DMARC) - get from sending domain
        $deliverability = [
            'dkim' => false,
            'spf' => false,
            'dmarc' => false,
        ];
        
        if ($campaign->from_email) {
            $domain = substr(strrchr($campaign->from_email, "@"), 1);
            $sendingDomain = \App\Models\SendingDomain::where('domain', $domain)
                ->where('customer_id', $campaign->customer_id)
                ->first();
            
            if ($sendingDomain && $sendingDomain->status === 'verified') {
                $deliverability['dkim'] = true;
                $deliverability['spf'] = true;
                $deliverability['dmarc'] = true;
            }
        }

        $stats = [
            'total_recipients' => $totalRecipients,
            'sent_count' => $campaign->sent_count ?? 0,
            'delivered' => $delivered,
            'pending_count' => $recipientStatuses['pending'] ?? 0,
            'opened_count' => $uniqueOpens, // Use unique opens instead of total open events
            'clicked_count' => $campaign->clicked_count ?? 0,
            'bounced_count' => $campaign->bounced_count ?? 0,
            'failed_count' => $campaign->failed_count ?? 0,
            'unsubscribed_count' => $campaign->unsubscribed_count ?? 0,
            'complained_count' => $campaign->complained_count ?? 0,
            'open_rate' => $openRate,
            'click_rate' => $clickRate,
            'bounce_rate' => $bounceRate,
            'failure_rate' => $failureRate,
            'delivery_rate' => $deliveryRate,
            'sending_speed' => $sendingSpeed,
            'recipient_statuses' => $recipientStatuses,
            'top_links' => $topLinks,
            'error_breakdown' => $errorBreakdown,
            'deliverability' => $deliverability,
        ];
        
        return view('customer.campaigns.show', compact('campaign', 'stats', 'runPreflightIssues'));
    }

    /**
     * Show campaign recipients table.
     */
    public function recipients(Campaign $campaign, Request $request)
    {
        $this->authorizeOwnership($campaign);

        $query = $campaign->recipients()->with([
            'logs' => function ($q) {
                $q->where('event', 'clicked')
                    ->whereNotNull('url')
                    ->orderBy('created_at');
            },
        ]);

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $recipients = $query->latest('created_at')->paginate(50);

        return view('customer.campaigns.recipients', compact('campaign', 'recipients'));
    }

    public function replies(Campaign $campaign, Request $request)
    {
        $this->authorizeOwnership($campaign);

        $query = $campaign->replies()->with('recipient');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('from_email', 'like', "%{$search}%")
                    ->orWhere('from_name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('recipient', function ($sub) use ($search) {
                        $sub->where('email', 'like', "%{$search}%");
                    });
            });
        }

        $replies = $query
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(25);

        return view('customer.campaigns.replies', compact('campaign', 'replies'));
    }

    /**
     * Get campaign stats (AJAX endpoint for real-time updates).
     */
    public function stats(Campaign $campaign)
    {
        $this->authorizeOwnership($campaign);

        $campaign->refresh();
        $campaign->load(['recipients', 'logs']);

        // Calculate total recipients - use actual count if campaign has started, otherwise calculate expected
        $totalRecipients = $campaign->total_recipients;
        if ($campaign->total_recipients === 0 && $campaign->emailList) {
            // Calculate expected recipients for campaigns that haven't started yet
            $totalRecipients = $this->calculateExpectedRecipients($campaign);
        }

        // Calculate delivered count (sent - bounced)
        $delivered = max(0, $campaign->sent_count - $campaign->bounced_count);
        
        // Recipient status breakdown
        $recipientStatuses = $campaign->recipients()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Calculate unique opens (recipients who opened at least once)
        $uniqueOpens = ($recipientStatuses['opened'] ?? 0) + ($recipientStatuses['clicked'] ?? 0);
        
        // Calculate rates based on unique opens
        $openRate = $delivered > 0 ? round(($uniqueOpens / $delivered) * 100, 2) : 0;
        $clickRate = $delivered > 0 ? round(($campaign->clicked_count / $delivered) * 100, 2) : 0;
        $bounceRate = $campaign->sent_count > 0 ? round(($campaign->bounced_count / $campaign->sent_count) * 100, 2) : 0;
        $failureRate = $campaign->sent_count > 0 ? round(($campaign->failed_count / $campaign->sent_count) * 100, 2) : 0;
        $deliveryRate = $totalRecipients > 0 
            ? round(($delivered / $totalRecipients) * 100, 2)
            : 0;

        // Calculate sending speed (emails per second) - based on recent activity
        $sendingSpeed = 0;
        if ($campaign->started_at && $campaign->sent_count > 0) {
            // Get recent activity from last 30 seconds to calculate current rate
            $recentActivity = $campaign->recipients()
                ->where('status', 'sent')
                ->where('updated_at', '>=', now()->subSeconds(30))
                ->count();
            
            if ($recentActivity > 0) {
                $sendingSpeed = round($recentActivity / 30, 2);
            } else {
                // Fallback: calculate average over last 5 minutes if no recent activity
                $fiveMinutesAgo = $campaign->recipients()
                    ->where('status', 'sent')
                    ->where('updated_at', '>=', now()->subMinutes(5))
                    ->count();
                
                if ($fiveMinutesAgo > 0) {
                    $sendingSpeed = round($fiveMinutesAgo / 300, 2);
                } else {
                    // Final fallback: show average over entire campaign
                    $secondsElapsed = max(1, now()->diffInSeconds($campaign->started_at));
                    $sendingSpeed = round($campaign->sent_count / $secondsElapsed, 2);
                }
            }
        }

        // Top clicked links
        $topLinks = $campaign->logs()
            ->where('event', 'clicked')
            ->whereNotNull('url')
            ->selectRaw('url, COUNT(*) as clicks')
            ->groupBy('url')
            ->orderByDesc('clicks')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'url' => $log->url,
                    'clicks' => $log->clicks,
                ];
            });

        // Error breakdown
        $errorBreakdown = $campaign->recipients()
            ->where('status', 'failed')
            ->whereNotNull('failure_reason')
            ->selectRaw('failure_reason, COUNT(*) as count')
            ->groupBy('failure_reason')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'failure_reason')
            ->toArray();

        $stats = [
            'status' => $campaign->status,
            'total_recipients' => $totalRecipients,
            'sent_count' => $campaign->sent_count ?? 0,
            'delivered' => $delivered,
            'pending_count' => $recipientStatuses['pending'] ?? 0,
            'opened_count' => $uniqueOpens, // Use unique opens instead of total open events
            'clicked_count' => $campaign->clicked_count ?? 0,
            'replied_count' => $campaign->replied_count ?? 0,
            'bounced_count' => $campaign->bounced_count ?? 0,
            'failed_count' => $campaign->failed_count ?? 0,
            'unsubscribed_count' => $campaign->unsubscribed_count ?? 0,
            'complained_count' => $campaign->complained_count ?? 0,
            'open_rate' => $openRate,
            'click_rate' => $clickRate,
            'bounce_rate' => $bounceRate,
            'failure_rate' => $failureRate,
            'delivery_rate' => $deliveryRate,
            'progress_percentage' => $totalRecipients > 0 
                ? round(($campaign->sent_count / $totalRecipients) * 100, 2)
                : 0,
            'sending_speed' => $sendingSpeed,
            'recipient_statuses' => $recipientStatuses,
            'top_links' => $topLinks,
            'error_breakdown' => $errorBreakdown,
            'started_at' => $campaign->started_at?->toIso8601String(),
            'finished_at' => $campaign->finished_at?->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campaign $campaign)
    {
        $this->authorizeOwnership($campaign);
        $customer = auth('customer')->user();
        $runPreflightIssues = [];

        if ($campaign->canStart()) {
            try {
                $this->campaignService->ensureCanRun($campaign);
            } catch (\RuntimeException $e) {
                $runPreflightIssues[] = $e->getMessage();
            }
        }
        $emailLists = EmailList::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->get();

        $templates = Template::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->orWhere(function ($subQ) {
                  $subQ->where('is_public', true)
                       ->where('is_system', false);
              });
        })
        ->whereIn('type', ['email', 'campaign'])
        ->get();

        $footerTemplates = Template::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->orWhere(function ($subQ) {
                  $subQ->where('is_public', true)
                       ->where('is_system', false);
              });
        })
        ->where('type', 'footer')
        ->get();

        $signatureTemplates = Template::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->orWhere(function ($subQ) {
                  $subQ->where('is_public', true)
                       ->where('is_system', false);
              });
        })
        ->where('type', 'signature')
        ->get();

        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $mustAddReply = (bool) $customer->groupSetting('servers.permissions.must_add_reply_server', false);
        $mustAddSending = (bool) $customer->groupSetting('domains.sending_domains.must_add', false);
        $mustAddTracking = (bool) $customer->groupSetting('domains.tracking_domains.must_add', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $deliveryServers = app(DeliveryServerService::class)->getSelectableDeliveryServersForCustomer(
            $customer,
            $mustAddDelivery,
            $canUseSystem
        );

        $replyServers = ReplyServer::query()
            ->where('active', true)
            ->when($mustAddReply, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->orderBy('name')
            ->get();

        $sendingDomains = SendingDomain::query()
            ->where('status', 'verified')
            ->when($mustAddSending, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->orderBy('domain')
            ->get();

        $trackingDomains = TrackingDomain::query()
            ->where('status', 'verified')
            ->when($mustAddTracking, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->orderBy('domain')
            ->get();

        $bounceServers = BounceServer::query()
            ->where('active', true)
            ->when((bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false), function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            })
            ->orderBy('name')
            ->get();

        $unlayerProjectId = config('services.unlayer.project_id');
        $unlayerDesign = $this->unlayerDesignFromCampaign($campaign);
        $campaignTagsByList = $this->buildCampaignTagsByList($emailLists);

        return view('customer.campaigns.edit', compact('campaign', 'emailLists', 'templates', 'footerTemplates', 'signatureTemplates', 'deliveryServers', 'replyServers', 'sendingDomains', 'trackingDomains', 'bounceServers', 'runPreflightIssues', 'unlayerProjectId', 'unlayerDesign', 'campaignTagsByList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $this->authorizeOwnership($campaign);
        $customer = auth('customer')->user();
        $mustAddReply = (bool) $customer->groupSetting('servers.permissions.must_add_reply_server', false);
        $mustAddDelivery = (bool) $customer->groupSetting('servers.permissions.must_add_delivery_server', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $selectableDeliveryServerIds = app(DeliveryServerService::class)
            ->getSelectableDeliveryServerIdsForCustomer($customer, $mustAddDelivery, $canUseSystem);

        $payload = $request->all();
        if (array_key_exists('delivery_server_id', $payload) && $payload['delivery_server_id'] === '') {
            $payload['delivery_server_id'] = null;
        }

        $validated = validator($payload, [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'list_id' => ['nullable', 'exists:email_lists,id'],
            'delivery_server_id' => [
                $mustAddDelivery ? 'required' : 'nullable',
                'integer',
                Rule::in($selectableDeliveryServerIds),
            ],
            'reply_server_id' => [
                'nullable',
                Rule::exists('reply_servers', 'id')->where(function ($q) use ($customer, $mustAddReply, $canUseSystem) {
                    $q->where('active', true);

                    if ($mustAddReply || !$canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        return;
                    }

                    $q->where(function ($sub) use ($customer) {
                        $sub->where('customer_id', $customer->id)
                            ->orWhereNull('customer_id');
                    });
                }),
            ],
            'sending_domain_id' => ['nullable', 'exists:sending_domains,id'],
            'tracking_domain_id' => ['nullable', 'exists:tracking_domains,id'],
            'bounce_server_id' => [
                'nullable',
                Rule::exists('bounce_servers', 'id')->where(function ($q) use ($customer) {
                    $q->where('active', true);
                    $mustAddBounce = (bool) $customer->groupSetting('servers.permissions.must_add_bounce_server', false);
                    $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);
                    
                    if ($mustAddBounce || !$canUseSystem) {
                        $q->where('customer_id', $customer->id);
                        return;
                    }
                    
                    $q->where(function ($sub) use ($customer) {
                        $sub->where('customer_id', $customer->id)
                            ->orWhereNull('customer_id');
                    });
                }),
            ],
            'template_id' => ['nullable', 'exists:templates,id'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'type' => ['nullable', 'in:regular,autoresponder,recurring'],
            'status' => ['nullable', 'in:draft,queued,scheduled,running,paused,completed,failed'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'template_data' => ['nullable'],
            'footer_template_id' => [
                'nullable',
                Rule::exists('templates', 'id')->where(function ($q) use ($customer) {
                    $q->where('type', 'footer')
                        ->whereNull('deleted_at')
                        ->where(function ($subQ) use ($customer) {
                            $subQ->where('customer_id', $customer->id)
                                ->orWhere(function ($inner) {
                                    $inner->where('is_public', true)->where('is_system', false);
                                });
                        });
                }),
            ],
            'signature_template_id' => [
                'nullable',
                Rule::exists('templates', 'id')->where(function ($q) use ($customer) {
                    $q->where('type', 'signature')
                        ->whereNull('deleted_at')
                        ->where(function ($subQ) use ($customer) {
                            $subQ->where('customer_id', $customer->id)
                                ->orWhere(function ($inner) {
                                    $inner->where('is_public', true)->where('is_system', false);
                                });
                        });
                }),
            ],
            'send_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date'],
            'track_opens' => ['nullable', 'boolean'],
            'track_clicks' => ['nullable', 'boolean'],
            'enable_spintax' => ['nullable', 'boolean'],
            'spam_scoring_enabled' => ['nullable', 'boolean'],
        ])->validate();

        $unlayerData = $this->buildUnlayerTemplateData($request->input('template_data'));
        if ($unlayerData !== null) {
            $validated['template_data'] = $unlayerData;
        } else {
            unset($validated['template_data']);
        }

        if (empty($validated['plain_text_content']) && !empty($validated['html_content'])) {
            $validated['plain_text_content'] = trim(preg_replace('/\s+/', ' ', strip_tags($validated['html_content'])));
        }

        $customerTimezone = $customer->timezone ?? 'UTC';
        if (!empty($validated['send_at'])) {
            $validated['send_at'] = Carbon::parse($validated['send_at'], $customerTimezone)->utc();
        }
        if (!empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = Carbon::parse($validated['scheduled_at'], $customerTimezone)->utc();
        }

        // Convert empty string to null for nullable fields
        if (isset($validated['delivery_server_id']) && $validated['delivery_server_id'] === '') {
            $validated['delivery_server_id'] = null;
        }
        if (isset($validated['reply_server_id']) && $validated['reply_server_id'] === '') {
            $validated['reply_server_id'] = null;
        }
        if (isset($validated['bounce_server_id']) && $validated['bounce_server_id'] === '') {
            $validated['bounce_server_id'] = null;
        }
        if (isset($validated['sending_domain_id']) && $validated['sending_domain_id'] === '') {
            $validated['sending_domain_id'] = null;
        }
        if (isset($validated['tracking_domain_id']) && $validated['tracking_domain_id'] === '') {
            $validated['tracking_domain_id'] = null;
        }
        if (isset($validated['list_id']) && $validated['list_id'] === '') {
            $validated['list_id'] = null;
        }
        if (isset($validated['template_id']) && $validated['template_id'] === '') {
            $validated['template_id'] = null;
        }

        $footerTemplateId = $validated['footer_template_id'] ?? null;
        $signatureTemplateId = $validated['signature_template_id'] ?? null;
        unset($validated['footer_template_id'], $validated['signature_template_id']);

        $settings = array_replace((array) ($campaign->settings ?? []), (array) ($validated['settings'] ?? []));
        if (!empty($footerTemplateId)) {
            $settings['footer_template_id'] = (int) $footerTemplateId;
        }
        if (!empty($signatureTemplateId)) {
            $settings['signature_template_id'] = (int) $signatureTemplateId;
        }
        
        // Update spintax and spam scoring settings
        if (isset($validated['enable_spintax'])) {
            $settings['enable_spintax'] = (bool) $validated['enable_spintax'];
        }
        if (isset($validated['spam_scoring_enabled'])) {
            $settings['spam_scoring_enabled'] = (bool) $validated['spam_scoring_enabled'];
        }
        
        if (!empty($settings)) {
            $validated['settings'] = $settings;
        }

        // Remove from validated as they're now in settings
        unset($validated['enable_spintax'], $validated['spam_scoring_enabled']);

        if (!empty($validated['send_at']) && empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = $validated['send_at'];
            $validated['status'] = 'scheduled';
        }

        $this->campaignService->update($campaign, $validated);

        return redirect()
            ->route('customer.campaigns.show', $campaign)
            ->with('success', 'Campaign updated successfully.');
    }

    /**
     * Preview spam score for current draft content.
     */
    public function previewSpamScore(Request $request, SpamScoringService $spamScoringService)
    {
        $payload = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'plain_text_content' => ['nullable', 'string'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'delivery_server_id' => ['nullable', 'integer'],
            'delivery_server_type' => ['nullable', 'string', 'max:100'],
            'delivery_server_from_email' => ['nullable', 'email', 'max:255'],
            'reply_server_id' => ['nullable', 'integer'],
        ]);

        $subject = trim((string) ($payload['subject'] ?? ''));
        $htmlContent = (string) ($payload['html_content'] ?? '');
        $plainTextContent = (string) ($payload['plain_text_content'] ?? '');

        if ($plainTextContent === '' && $htmlContent !== '') {
            $plainTextContent = trim(preg_replace('/\s+/', ' ', strip_tags($htmlContent)));
        }

        $fromEmail = $payload['from_email'] ?? (auth('customer')->user()->email ?? '');
        $replyTo = $payload['reply_to'] ?? $fromEmail;
        $fromName = trim((string) ($payload['from_name'] ?? ''));
        $deliveryServerId = $payload['delivery_server_id'] ?? null;
        $deliveryServerType = trim((string) ($payload['delivery_server_type'] ?? ''));
        $deliveryServerFromEmail = trim((string) ($payload['delivery_server_from_email'] ?? ''));
        $replyServerId = $payload['reply_server_id'] ?? null;

        $result = $spamScoringService->calculateSpamScore(
            $subject,
            $htmlContent,
            $plainTextContent,
            [
                'from_name' => $fromName,
                'from_email' => $fromEmail,
                'reply_to' => $replyTo,
                'delivery_server_id' => $deliveryServerId,
                'delivery_server_type' => $deliveryServerType,
                'delivery_server_from_email' => $deliveryServerFromEmail,
                'reply_server_id' => $replyServerId,
            ]
        );

        return response()->json([
            'ok' => true,
            'result' => $result,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $this->authorizeOwnership($campaign);
        $this->campaignService->delete($campaign);

        return redirect()
            ->route('customer.campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    public function duplicate(Campaign $campaign)
    {
        $this->authorizeOwnership($campaign);

        $customer = auth('customer')->user();
        $customer->enforceGroupLimit('campaigns.limits.max_campaigns', $customer->campaigns()->count(), 'Campaign limit reached.');

        $campaign->loadMissing('variants');

        $copy = DB::transaction(function () use ($campaign) {
            $clone = $campaign->replicate();

            $clone->name = Str::limit((string) ($campaign->name ?? 'Campaign') . ' (Copy)', 255, '');
            $clone->status = 'draft';
            $clone->failure_reason = null;
            $clone->scheduled_at = null;
            $clone->send_at = null;
            $clone->started_at = null;
            $clone->finished_at = null;

            $clone->total_recipients = 0;
            $clone->sent_count = 0;
            $clone->delivered_count = 0;
            $clone->opened_count = 0;
            $clone->clicked_count = 0;
            $clone->failed_count = 0;
            $clone->bounced_count = 0;
            $clone->unsubscribed_count = 0;
            $clone->complained_count = 0;
            $clone->replied_count = 0;
            $clone->open_rate = 0;
            $clone->click_rate = 0;
            $clone->bounce_rate = 0;

            $clone->save();

            if ($campaign->relationLoaded('variants') && $campaign->variants->isNotEmpty()) {
                foreach ($campaign->variants as $variant) {
                    $clone->variants()->create([
                        'name' => $variant->name,
                        'subject' => $variant->subject,
                        'html_content' => $variant->html_content,
                        'plain_text_content' => $variant->plain_text_content,
                        'split_percentage' => $variant->split_percentage,
                        'total_recipients' => 0,
                        'sent_count' => 0,
                        'delivered_count' => 0,
                        'opened_count' => 0,
                        'clicked_count' => 0,
                        'bounced_count' => 0,
                        'unsubscribed_count' => 0,
                        'open_rate' => 0,
                        'click_rate' => 0,
                        'bounce_rate' => 0,
                        'is_winner' => false,
                        'sent_at' => null,
                    ]);
                }
            }

            return $clone;
        });

        return redirect()
            ->route('customer.campaigns.edit', $copy)
            ->with('success', 'Campaign duplicated successfully.');
    }

    /**
     * Start a campaign (queue-based).
     */
    public function start(Campaign $campaign)
    {
        // Check if campaign belongs to the authenticated customer
        if ((int) $campaign->customer_id !== (int) auth('customer')->id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($campaign->type === 'recurring') {
            if (!$campaign->scheduled_at || $campaign->scheduled_at->isPast()) {
                $campaign->update([
                    'status' => 'scheduled',
                    'scheduled_at' => now(),
                ]);
            } else {
                $campaign->update([
                    'status' => 'scheduled',
                ]);
            }

            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('success', 'Recurring campaign has been scheduled and will run automatically.');
        }

        // Validate campaign can be started
        if (!$campaign->canStart()) {
            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', 'Campaign cannot be started. Only draft or scheduled campaigns can be started.');
        }

        try {
            $this->campaignService->ensureCanRun($campaign);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', $e->getMessage());
        }

        // Validate campaign is ready to send
        if (!$campaign->list_id) {
            $campaign->update([
                'status' => 'failed',
                'failure_reason' => 'Campaign must have an email list selected.',
            ]);
            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', 'Campaign must have an email list selected.');
        }

        if (!$campaign->html_content && !$campaign->plain_text_content) {
            $campaign->update([
                'status' => 'failed',
                'failure_reason' => 'Campaign must have content (HTML or plain text).',
            ]);
            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', 'Campaign must have content (HTML or plain text).');
        }

        // Check if email list has confirmed subscribers
        if ($campaign->emailList && $campaign->emailList->subscribers()->where('status', 'confirmed')->count() === 0) {
            $campaign->update([
                'status' => 'failed',
                'failure_reason' => 'Email list has no confirmed subscribers. Please add subscribers to the list first.',
            ]);
            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', 'Email list has no confirmed subscribers. Please add subscribers to the list first.');
        }

        $queueConnection = config('queue.default', 'sync');
        if ($queueConnection === 'sync') {
            Log::warning(
                "Campaign {$campaign->id} started with sync queue. " .
                "Jobs will run synchronously. Consider using 'database' or 'redis' queue connection."
            );
        }

        if ($campaign->scheduled_at && $campaign->scheduled_at->isFuture() && $queueConnection !== 'sync') {
            $campaign->update([
                'status' => 'scheduled',
            ]);

            StartCampaignJob::dispatch($campaign)
                ->delay($campaign->scheduled_at)
                ->onQueue('campaigns');

            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('success', 'Campaign has been scheduled and will start automatically at the selected time.');
        }

        try {
            $campaign->update([
                'status' => 'queued',
            ]);

            // Dispatch start job to queue (non-blocking)
            // IMPORTANT: This will only work if QUEUE_CONNECTION is NOT 'sync'
            // If using 'sync', jobs run immediately and synchronously
            StartCampaignJob::dispatch($campaign)
                ->onQueue('campaigns');

            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('success', 'Campaign has been queued to start. ' . 
                    ($queueConnection === 'sync' 
                        ? 'Note: Queue is set to sync - emails will send synchronously. ' .
                          'For background processing, change QUEUE_CONNECTION to "database" and run: php artisan queue:work'
                        : 'It will begin sending shortly in the background.'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to dispatch StartCampaignJob for campaign {$campaign->id}: " . $e->getMessage());
            
            $campaign->update([
                'status' => 'failed',
                'failure_reason' => 'Failed to queue campaign: ' . $e->getMessage(),
            ]);

            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', 'Failed to start campaign: ' . $e->getMessage());
        }
    }

    /**
     * Pause a running campaign.
     */
    public function pause(Campaign $campaign)
    {
        // Check if campaign belongs to the authenticated customer
        if ((int) $campaign->customer_id !== (int) auth('customer')->id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$campaign->canPause()) {
            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', 'Campaign cannot be paused. Only running campaigns can be paused.');
        }

        DB::transaction(function () use ($campaign) {
            $oldStatus = $campaign->status;
            $campaign->update([
                'status' => 'paused',
            ]);

            if ($campaign->customer) {
                $campaign->customer->notify(
                    new CampaignStatusUpdatedNotification($campaign, $oldStatus, 'paused')
                );
            }
        });

        return redirect()
            ->route('customer.campaigns.show', $campaign)
            ->with('success', 'Campaign has been paused. Jobs will stop processing automatically.');
    }

    /**
     * Resume a paused campaign.
     */
    public function resume(Campaign $campaign)
    {
        // Check if campaign belongs to the authenticated customer
        if ((int) $campaign->customer_id !== (int) auth('customer')->id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$campaign->canResume()) {
            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', 'Campaign cannot be resumed. Only paused campaigns can be resumed.');
        }

        // Sync stats first to ensure accuracy
        $campaign->syncStats();

        // Get pending recipients (also include failed recipients that can be retried)
        $pendingRecipients = $campaign->recipients()
            ->whereIn('status', ['pending', 'failed'])
            ->pluck('id')
            ->chunk(50);

        if ($pendingRecipients->isEmpty()) {
            // Check if there are any recipients at all
            $totalRecipients = $campaign->recipients()->count();
            if ($totalRecipients === 0) {
                return redirect()
                    ->route('customer.campaigns.show', $campaign)
                    ->with('error', 'No recipients found for this campaign.');
            }
            
            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', 'No pending or failed recipients to resume sending. All recipients have been processed.');
        }

        // Reset failed recipients back to pending so they can be retried
        $campaign->recipients()
            ->where('status', 'failed')
            ->update([
                'status' => 'pending',
                'failed_at' => null,
                'failure_reason' => null,
            ]);

        DB::transaction(function () use ($campaign) {
            $oldStatus = $campaign->status;
            $campaign->update([
                'status' => 'running',
            ]);

            if ($campaign->customer) {
                $campaign->customer->notify(
                    new CampaignStatusUpdatedNotification($campaign, $oldStatus, 'running')
                );
            }
        });

        // Dispatch remaining chunks
        foreach ($pendingRecipients as $chunk) {
            \App\Jobs\SendCampaignChunkJob::dispatch($campaign, $chunk->toArray())
                ->onQueue('campaigns');
        }

        return redirect()
            ->route('customer.campaigns.show', $campaign)
            ->with('success', 'Campaign has been resumed. Remaining emails will be sent.');
    }

    /**
     * Rerun a failed campaign.
     */
    public function rerun(Campaign $campaign)
    {
        // Check if campaign belongs to the authenticated customer
        if ((int) $campaign->customer_id !== (int) auth('customer')->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Allow rerun for failed or completed campaigns
        if (!($campaign->isFailed() || $campaign->isCompleted())) {
            return redirect()
                ->route('customer.campaigns.show', $campaign)
                ->with('error', 'Campaign cannot be rerun. Only failed or completed campaigns can be rerun.');
        }

        // Reset campaign status and clear failure reason
        DB::transaction(function () use ($campaign) {
            $oldStatus = $campaign->status;
            $campaign->update([
                'status' => 'draft',
                'failure_reason' => null,
                'started_at' => null,
                'finished_at' => null,
            ]);

            // Optionally clear recipient records to start fresh
            // Uncomment if you want to clear previous recipient records
            // $campaign->recipients()->delete();
            if ($campaign->customer) {
                $campaign->customer->notify(
                    new CampaignStatusUpdatedNotification($campaign, $oldStatus, 'draft')
                );
            }
        });

        return redirect()
            ->route('customer.campaigns.show', $campaign)
            ->with('success', 'Campaign has been reset. You can now start it again.');
    }

    /**
     * Show A/B testing page for a campaign.
     */
    public function showAbTest(Campaign $campaign)
    {
        // Check if campaign belongs to the authenticated customer
        if ((int) $campaign->customer_id !== (int) auth('customer')->id()) {
            abort(403, 'Unauthorized action.');
        }

        $campaign->load(['emailList', 'variants']);
        
        return view('customer.campaigns.ab-test', compact('campaign'));
    }

    /**
     * Store A/B test variants.
     */
    public function storeAbTest(Request $request, Campaign $campaign)
    {
        // Check if campaign belongs to the authenticated customer
        if ((int) $campaign->customer_id !== (int) auth('customer')->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'variants' => ['required', 'array', 'min:2', 'max:5'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.subject' => ['nullable', 'string', 'max:255'],
            'variants.*.html_content' => ['nullable', 'string'],
            'variants.*.plain_text_content' => ['nullable', 'string'],
            'variants.*.split_percentage' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        // Validate split percentages add up to 100
        $totalPercentage = array_sum(array_column($validated['variants'], 'split_percentage'));
        if ($totalPercentage !== 100) {
            return redirect()
                ->route('customer.campaigns.ab-test', $campaign)
                ->with('error', 'Split percentages must add up to 100%.')
                ->withInput();
        }

        // Delete existing variants
        $campaign->variants()->delete();

        // Create new variants
        foreach ($validated['variants'] as $variantData) {
            $campaign->variants()->create([
                'name' => $variantData['name'],
                'subject' => $variantData['subject'] ?? $campaign->subject,
                'html_content' => $variantData['html_content'] ?? $campaign->html_content,
                'plain_text_content' => $variantData['plain_text_content'] ?? $campaign->plain_text_content,
                'split_percentage' => $variantData['split_percentage'],
            ]);
        }

        return redirect()
            ->route('customer.campaigns.ab-test', $campaign)
            ->with('success', 'A/B test variants created successfully.');
    }

    /**
     * Mark a variant as winner and send to remaining audience.
     */
    public function selectWinner(Request $request, Campaign $campaign, CampaignVariant $variant)
    {
        // Check if campaign belongs to the authenticated customer
        if ((int) $campaign->customer_id !== (int) auth('customer')->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if variant belongs to campaign
        if ($variant->campaign_id !== $campaign->id) {
            abort(404, 'Variant not found.');
        }

        // Mark all variants as not winner
        $campaign->variants()->update(['is_winner' => false]);
        
        // Mark selected variant as winner
        $variant->update(['is_winner' => true]);

        // Send winning variant to remaining audience if requested
        if ($request->has('send_to_remaining')) {
            $this->campaignService->sendWinningVariant($campaign, $variant);
        }

        return redirect()
            ->route('customer.campaigns.ab-test', $campaign)
            ->with('success', 'Winner selected successfully.');
    }

    /**
     * Calculate expected recipients for a campaign that hasn't started yet.
     */
    private function calculateExpectedRecipients(Campaign $campaign): int
    {
        if (!$campaign->emailList) {
            return 0;
        }

        // Get confirmed subscribers from the email list
        // Exclude bounced, complained, and suppressed subscribers (same logic as StartCampaignJob)
        $query = $campaign->emailList->subscribers()
            ->where('status', 'confirmed')
            ->where('is_bounced', false)
            ->where('is_complained', false)
            ->whereNull('suppressed_at');

        // Also check global suppression list
        $suppressedEmails = \App\Models\SuppressionList::where('customer_id', $campaign->customer_id)
            ->pluck('email')
            ->toArray();

        if (!empty($suppressedEmails)) {
            $query->whereNotIn('email', $suppressedEmails);
        }

        return $query->count();
    }
}
