<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\StartEmailValidationRunJob;
use App\Models\EmailList;
use App\Models\EmailValidationRun;
use App\Models\EmailValidationRunItem;
use App\Models\EmailValidationTool;
use App\Jobs\ProcessEmailValidationChunkJob;
use App\Services\Billing\UsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailValidationRunController extends Controller
{
    private const META_ALLOWED_GROUP_IDS_KEY = 'allowed_customer_group_ids';

    protected function customerGroupIds($customer): array
    {
        if (!$customer) {
            return [];
        }

        $groups = $customer->relationLoaded('customerGroups')
            ? $customer->customerGroups
            : $customer->customerGroups()->get();

        return $groups
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    protected function globalToolAllowedForCustomer(EmailValidationTool $tool, $customer): bool
    {
        if ($tool->customer_id !== null) {
            return false;
        }

        $allowedGroupIds = (array) data_get($tool->meta ?? [], self::META_ALLOWED_GROUP_IDS_KEY, []);
        $allowedGroupIds = array_values(array_unique(array_filter(array_map('intval', $allowedGroupIds), fn ($id) => $id > 0)));

        if (empty($allowedGroupIds)) {
            return true;
        }

        $customerGroupIds = $this->customerGroupIds($customer);
        if (empty($customerGroupIds)) {
            return false;
        }

        return count(array_intersect($allowedGroupIds, $customerGroupIds)) > 0;
    }
    protected function authorizeRun(EmailValidationRun $run): EmailValidationRun
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $run->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $run;
    }

    protected function authorizeToolView(EmailValidationTool $tool): EmailValidationTool
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if ((int) $tool->customer_id === (int) $customer->id) {
            return $tool;
        }

        $mustAddOwn = (bool) $customer->groupSetting('email_validation.must_add', false);

        if (!$mustAddOwn && $tool->customer_id === null && $this->globalToolAllowedForCustomer($tool, $customer)) {
            return $tool;
        }

        abort(404);
    }

    public function index()
    {
        $customer = auth('customer')->user();

        $runs = EmailValidationRun::query()
            ->where('customer_id', $customer->id)
            ->with(['list', 'tool'])
            ->latest()
            ->paginate(15);

        return view('customer.email-validation.runs.index', compact('runs'));
    }

    public function create()
    {
        $customer = auth('customer')->user();

        $lists = EmailList::query()
            ->where('customer_id', $customer->id)
            ->orderBy('name')
            ->get();

        if ($lists->isEmpty()) {
            return redirect()
                ->route('customer.lists.index')
                ->with('error', 'Please create an email list first.');
        }

        $mustAddOwn = (bool) $customer->groupSetting('email_validation.must_add', false);

        $customerGroupIds = $this->customerGroupIds($customer);

        $tools = EmailValidationTool::query()
            ->when($mustAddOwn, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer) {
                $q->where(function ($sub) use ($customer) {
                    $sub->where('customer_id', $customer->id)
                        ->orWhereNull('customer_id');
                });
            })
            ->when(!$mustAddOwn, function ($query) use ($customerGroupIds) {
                $query->where(function ($sub) use ($customerGroupIds) {
                    $sub->whereNotNull('customer_id')
                        ->orWhere(function ($global) use ($customerGroupIds) {
                            $global->whereNull('customer_id')
                                ->where(function ($scope) use ($customerGroupIds) {
                                    $scope->whereNull('meta')
                                        ->orWhereNull('meta->' . self::META_ALLOWED_GROUP_IDS_KEY)
                                        ->orWhereJsonLength('meta->' . self::META_ALLOWED_GROUP_IDS_KEY, 0);

                                    if (!empty($customerGroupIds)) {
                                        $scope->orWhere(function ($allowed) use ($customerGroupIds) {
                                            foreach ($customerGroupIds as $groupId) {
                                                $allowed->orWhereJsonContains('meta->' . self::META_ALLOWED_GROUP_IDS_KEY, (int) $groupId);
                                            }
                                        });
                                    }
                                });
                        });
                });
            })
            ->where('active', true)
            ->orderBy('name')
            ->get();

        if ($tools->isEmpty()) {
            return redirect()
                ->route('customer.email-validation.tools.create')
                ->with('error', 'Please add an email validation tool first.');
        }

        return view('customer.email-validation.runs.create', compact('lists', 'tools'));
    }

    public function store(Request $request)
    {
        $customer = auth('customer')->user();

        $validated = $request->validate([
            'list_id' => ['required', 'integer'],
            'tool_id' => ['required', 'integer'],
            'invalid_action' => ['required', 'in:unsubscribe,mark_spam,delete'],
        ]);

        $list = EmailList::query()
            ->where('customer_id', $customer->id)
            ->findOrFail($validated['list_id']);

        $tool = EmailValidationTool::query()->findOrFail($validated['tool_id']);
        $this->authorizeToolView($tool);

        $total = DB::table('list_subscribers')
            ->where('list_id', $list->id)
            ->whereNull('deleted_at')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->distinct()
            ->count('email');

        $monthlyLimit = (int) $customer->groupSetting('email_validation.monthly_limit', 0);
        if ($monthlyLimit > 0) {
            $usage = app(UsageService::class)->getUsage($customer);
            $current = (int) ($usage['email_validation_emails_this_month'] ?? 0);
            $remaining = $monthlyLimit - $current;

            if ($remaining < $total) {
                return back()->with('error', "Monthly validation limit exceeded. Remaining: {$remaining}.");
            }
        }

        $run = EmailValidationRun::create([
            'customer_id' => $customer->id,
            'list_id' => $list->id,
            'tool_id' => $tool->id,
            'status' => 'pending',
            'invalid_action' => $validated['invalid_action'],
            'total_emails' => $total,
            'processed_count' => 0,
            'deliverable_count' => 0,
            'undeliverable_count' => 0,
            'accept_all_count' => 0,
            'unknown_count' => 0,
            'error_count' => 0,
        ]);

        StartEmailValidationRunJob::dispatch($run)
            ->onQueue('email-validation');

        return redirect()
            ->route('customer.email-validation.runs.show', $run)
            ->with('success', 'Validation run created.');
    }

    public function show(EmailValidationRun $run)
    {
        $this->authorizeRun($run);

        $run->load(['list', 'tool']);

        return view('customer.email-validation.runs.show', compact('run'));
    }

    public function stats(EmailValidationRun $run)
    {
        $this->authorizeRun($run);

        $run->refresh();

        $settings = is_array($run->settings) ? $run->settings : [];
        $isPaused = (bool) data_get($settings, 'is_paused', false);
        $pauseReason = is_string(data_get($settings, 'pause_reason'))
            ? (string) data_get($settings, 'pause_reason')
            : null;

        $inboxFullCount = EmailValidationRunItem::query()
            ->where('run_id', $run->id)
            ->where('flags->inbox_full', true)
            ->count();

        $total = max(0, (int) $run->total_emails);
        $processed = max(0, (int) $run->processed_count);
        $percent = $total > 0 ? round(($processed / $total) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'stats' => [
                'status' => $run->status,
                'failure_reason' => $run->failure_reason,
                'total_emails' => $total,
                'processed_count' => $processed,
                'percent' => $percent,
                'is_paused' => $isPaused,
                'pause_reason' => $pauseReason,
                'deliverable_count' => (int) $run->deliverable_count,
                'undeliverable_count' => (int) $run->undeliverable_count,
                'inbox_full_count' => (int) $inboxFullCount,
                'accept_all_count' => (int) $run->accept_all_count,
                'unknown_count' => (int) $run->unknown_count,
                'error_count' => (int) $run->error_count,
            ],
        ]);
    }

    public function errors(Request $request, EmailValidationRun $run)
    {
        $this->authorizeRun($run);

        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min(200, $limit));

        $items = EmailValidationRunItem::query()
            ->where('run_id', $run->id)
            ->where('success', false)
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'email', 'result', 'message', 'raw', 'validated_at'])
            ->map(function ($item) {
                $raw = is_array($item->raw ?? null) ? $item->raw : [];
                $status = is_numeric($raw['status'] ?? null) ? (int) $raw['status'] : null;
                $body = is_string($raw['body'] ?? null) ? (string) $raw['body'] : null;

                return [
                    'id' => $item->id,
                    'email' => $item->email,
                    'result' => $item->result,
                    'message' => $item->message,
                    'validated_at' => $item->validated_at,
                    'provider_status' => $status,
                    'provider_body_snippet' => is_string($body) ? mb_substr($body, 0, 500) : null,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    public function pause(Request $request, EmailValidationRun $run)
    {
        $this->authorizeRun($run);

        $run->refresh();

        if ($run->status !== 'running') {
            return back();
        }

        $settings = is_array($run->settings) ? $run->settings : [];
        $settings['paused_by'] = 'user';
        $settings['is_paused'] = true;
        unset($settings['auto_cycle_resume_at']);

        $run->update([
            'settings' => $settings,
        ]);

        return back()->with('success', 'Validation paused.');
    }

    public function resume(Request $request, EmailValidationRun $run)
    {
        $this->authorizeRun($run);

        $run->refresh();

        if ($run->status !== 'running') {
            return back();
        }

        $settings = is_array($run->settings) ? $run->settings : [];
        $settings['paused_by'] = null;
        $settings['is_paused'] = false;
        unset($settings['auto_cycle_resume_at']);

        $run->update([
            'settings' => $settings,
        ]);

        return back()->with('success', 'Validation resumed.');
    }

    public function resumeFailed(Request $request, EmailValidationRun $run)
    {
        $this->authorizeRun($run);

        $run->refresh();

        if ($run->status !== 'failed') {
            return back();
        }

        $settings = is_array($run->settings) ? $run->settings : [];
        $settings['is_paused'] = false;
        $settings['paused_by'] = null;
        unset($settings['auto_cycle_resume_at']);

        $run->update([
            'status' => 'running',
            'finished_at' => null,
            'failure_reason' => null,
            'settings' => $settings,
        ]);

        $baseQuery = DB::table('list_subscribers')
            ->where('list_id', $run->list_id)
            ->whereNull('deleted_at')
            ->whereNotNull('email')
            ->where('email', '!=', '');

        $distinctSubquery = (clone $baseQuery)
            ->selectRaw('MIN(id) as id, email')
            ->groupBy('email');

        $remainingQuery = DB::query()
            ->fromSub($distinctSubquery, 't')
            ->leftJoin('email_validation_run_items as eri', function ($join) use ($run) {
                $join->on('eri.email', '=', 't.email')
                    ->where('eri.run_id', '=', $run->id);
            })
            ->whereNull('eri.id')
            ->select(['t.id'])
            ->orderBy('t.id');

        $chunkSize = 100;
        $remainingQuery->chunkById($chunkSize, function ($rows) use ($run) {
            $ids = collect($rows)->pluck('id')->filter()->values()->all();
            if (empty($ids)) {
                return;
            }

            ProcessEmailValidationChunkJob::dispatch($run, $ids)
                ->onQueue('email-validation');
        }, 't.id');

        return back()->with('success', 'Validation resumed.');
    }
}
