<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailList;
use App\Models\ListSegment;
use App\Services\EmailListService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EmailListController extends Controller
{
    public function __construct(
        protected EmailListService $emailListService
    ) {
        $this->middleware('customer.access:lists.permissions.can_access_lists')->only(['index', 'show']);
        $this->middleware('customer.access:lists.permissions.can_create_lists')->only(['create', 'store']);
        $this->middleware('customer.access:lists.permissions.can_edit_lists')->only(['edit', 'update', 'storeTag', 'updateTag', 'destroyTag']);
        $this->middleware('customer.access:lists.permissions.can_delete_lists')->only(['destroy']);
    }

    protected function authorizeOwnership(EmailList $list): EmailList
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $list->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $list;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tab = (string) $request->input('tab', 'lists');
        $filters = $request->only(['search', 'status']);
        $customer = auth('customer')->user();
        $emailLists = $this->emailListService->getPaginated($customer, $filters);
        $performanceMap = $this->listPerformanceMap(
            (int) $customer->id,
            $emailLists->getCollection()->pluck('id')->all()
        );
        $this->appendPerformanceToLists($emailLists, $performanceMap);

        $segments = null;
        $segmentSearch = (string) $request->input('segment_search', '');
        $segmentSort = (string) $request->input('segment_sort', 'created_at_desc');

        $overview = null;
        $overviewCampaign = null;
        if ($tab === 'overview') {
            $overview = EmailList::query()
                ->where('customer_id', $customer->id)
                ->selectRaw('COUNT(*) as lists_count')
                ->selectRaw('COALESCE(SUM(subscribers_count), 0) as subscribers_count')
                ->selectRaw('COALESCE(SUM(confirmed_subscribers_count), 0) as confirmed_subscribers_count')
                ->selectRaw('COALESCE(SUM(unsubscribed_count), 0) as unsubscribed_count')
                ->selectRaw('COALESCE(SUM(bounced_count), 0) as bounced_count')
                ->first();

            $overviewCampaign = Campaign::query()
                ->where('customer_id', $customer->id)
                ->whereNotNull('list_id')
                ->selectRaw('COUNT(*) as campaigns_count')
                ->selectRaw('COALESCE(SUM(sent_count), 0) as sent_count')
                ->selectRaw('COALESCE(SUM(CASE WHEN sent_count > bounced_count THEN sent_count - bounced_count ELSE 0 END), 0) as delivered_count')
                ->selectRaw('COALESCE(SUM(opened_count), 0) as opened_count')
                ->selectRaw('COALESCE(SUM(clicked_count), 0) as clicked_count')
                ->first();

            $delivered = (int) ($overviewCampaign->delivered_count ?? 0);
            $overviewCampaign->open_rate = $delivered > 0
                ? round(((int) ($overviewCampaign->opened_count ?? 0) / $delivered) * 100, 2)
                : 0.0;
            $overviewCampaign->click_rate = $delivered > 0
                ? round(((int) ($overviewCampaign->clicked_count ?? 0) / $delivered) * 100, 2)
                : 0.0;
        }

        if ($tab === 'segments') {
            $segmentsQuery = ListSegment::query()
                ->whereHas('emailList', fn ($query) => $query->where('customer_id', $customer->id))
                ->with('emailList');

            if ($segmentSearch !== '') {
                $segmentsQuery->where(function ($query) use ($segmentSearch) {
                    $query->where('name', 'like', "%{$segmentSearch}%")
                        ->orWhere('description', 'like', "%{$segmentSearch}%");
                });
            }

            $segments = match ($segmentSort) {
                'created_at_asc' => $segmentsQuery->oldest('created_at')->paginate(15)->withQueryString(),
                'name_asc' => $segmentsQuery->orderBy('name')->paginate(15)->withQueryString(),
                'name_desc' => $segmentsQuery->orderByDesc('name')->paginate(15)->withQueryString(),
                'subscribers_desc' => $segmentsQuery->orderByDesc('subscribers_count')->paginate(15)->withQueryString(),
                default => $segmentsQuery->latest('created_at')->paginate(15)->withQueryString(),
            };
        }

        return view('customer.lists.index', compact('emailLists', 'filters', 'tab', 'overview', 'overviewCampaign', 'segments', 'segmentSearch', 'segmentSort'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customer.lists.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customer = auth('customer')->user();
        $customer->enforceGroupLimit('lists.limits.max_lists', $customer->emailLists()->count(), 'Email list limit reached.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'opt_in' => ['nullable', 'in:single,double'],
            'opt_out' => ['nullable', 'in:single,double'],
            'double_opt_in' => ['nullable', 'boolean'],
            'default_subject' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'welcome_email_enabled' => ['nullable', 'boolean'],
            'welcome_email_subject' => ['nullable', 'string', 'max:255'],
            'welcome_email_content' => ['nullable', 'string'],
            'unsubscribe_email_enabled' => ['nullable', 'boolean'],
            'unsubscribe_email_subject' => ['nullable', 'string', 'max:255'],
            'unsubscribe_email_content' => ['nullable', 'string'],
            'tags' => ['nullable'],
        ]);

        $validated['tags'] = $this->normalizeTags($request->input('tags'));

        $emailList = $this->emailListService->create($customer, $validated);

        return redirect()
            ->route('customer.lists.show', $emailList)
            ->with('success', 'Email list created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailList $list)
    {
        $this->authorizeOwnership($list);
        $list->load(['subscribers' => function ($query) {
            $query->latest()->limit(10);
        }]);

        $performance = $this->listPerformanceMap((int) $list->customer_id, [(int) $list->id]);
        $listPerformance = $performance[(int) $list->id] ?? [
            'campaigns_count' => 0,
            'sent_count' => 0,
            'delivered_count' => 0,
            'opened_count' => 0,
            'clicked_count' => 0,
            'open_rate' => 0.0,
            'click_rate' => 0.0,
        ];

        $recentCampaigns = Campaign::query()
            ->where('customer_id', (int) $list->customer_id)
            ->where('list_id', (int) $list->id)
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'name', 'status', 'sent_count', 'opened_count', 'clicked_count', 'bounced_count', 'created_at']);
        
        return view('customer.lists.show', compact('list', 'listPerformance', 'recentCampaigns'));
    }

    private function listPerformanceMap(int $customerId, array $listIds): array
    {
        $listIds = array_values(array_filter(array_map('intval', $listIds), static fn (int $id) => $id > 0));
        if ($listIds === []) {
            return [];
        }

        $rows = Campaign::query()
            ->where('customer_id', $customerId)
            ->whereIn('list_id', $listIds)
            ->groupBy('list_id')
            ->selectRaw('list_id')
            ->selectRaw('COUNT(*) as campaigns_count')
            ->selectRaw('COALESCE(SUM(sent_count), 0) as sent_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN sent_count > bounced_count THEN sent_count - bounced_count ELSE 0 END), 0) as delivered_count')
            ->selectRaw('COALESCE(SUM(opened_count), 0) as opened_count')
            ->selectRaw('COALESCE(SUM(clicked_count), 0) as clicked_count')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $listId = (int) ($row->list_id ?? 0);
            if ($listId <= 0) {
                continue;
            }

            $delivered = (int) ($row->delivered_count ?? 0);
            $opened = (int) ($row->opened_count ?? 0);
            $clicked = (int) ($row->clicked_count ?? 0);

            $map[$listId] = [
                'campaigns_count' => (int) ($row->campaigns_count ?? 0),
                'sent_count' => (int) ($row->sent_count ?? 0),
                'delivered_count' => $delivered,
                'opened_count' => $opened,
                'clicked_count' => $clicked,
                'open_rate' => $delivered > 0 ? round(($opened / $delivered) * 100, 2) : 0.0,
                'click_rate' => $delivered > 0 ? round(($clicked / $delivered) * 100, 2) : 0.0,
            ];
        }

        return $map;
    }

    private function appendPerformanceToLists(LengthAwarePaginator $emailLists, array $performanceMap): void
    {
        $collection = $emailLists->getCollection();

        if (!$collection instanceof Collection) {
            return;
        }

        $collection->transform(function (EmailList $list) use ($performanceMap) {
            $metrics = $performanceMap[(int) $list->id] ?? [
                'campaigns_count' => 0,
                'sent_count' => 0,
                'delivered_count' => 0,
                'opened_count' => 0,
                'clicked_count' => 0,
                'open_rate' => 0.0,
                'click_rate' => 0.0,
            ];

            $list->setAttribute('campaigns_count', $metrics['campaigns_count']);
            $list->setAttribute('sent_count', $metrics['sent_count']);
            $list->setAttribute('delivered_count', $metrics['delivered_count']);
            $list->setAttribute('opened_count', $metrics['opened_count']);
            $list->setAttribute('clicked_count', $metrics['clicked_count']);
            $list->setAttribute('open_rate', $metrics['open_rate']);
            $list->setAttribute('click_rate', $metrics['click_rate']);

            return $list;
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailList $list)
    {
        $this->authorizeOwnership($list);
        return view('customer.lists.edit', compact('list'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailList $list)
    {
        $this->authorizeOwnership($list);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'in:active,inactive,pending'],
            'opt_in' => ['nullable', 'in:single,double'],
            'opt_out' => ['nullable', 'in:single,double'],
            'double_opt_in' => ['nullable', 'boolean'],
            'default_subject' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'welcome_email_enabled' => ['nullable', 'boolean'],
            'welcome_email_subject' => ['nullable', 'string', 'max:255'],
            'welcome_email_content' => ['nullable', 'string'],
            'unsubscribe_email_enabled' => ['nullable', 'boolean'],
            'unsubscribe_email_subject' => ['nullable', 'string', 'max:255'],
            'unsubscribe_email_content' => ['nullable', 'string'],
            'tags' => ['nullable'],
        ]);

        $validated['tags'] = $this->normalizeTags($request->input('tags'));

        $this->emailListService->update($list, $validated);

        return redirect()
            ->route('customer.lists.show', $list)
            ->with('success', 'Email list updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailList $list)
    {
        $this->authorizeOwnership($list);
        $this->emailListService->delete($list);

        return redirect()
            ->route('customer.lists.index')
            ->with('success', 'Email list deleted successfully.');
    }

    public function storeTag(Request $request, EmailList $list)
    {
        $this->authorizeOwnership($list);

        $validated = $request->validate([
            'tag' => ['required', 'string', 'max:100'],
        ]);

        $existingTags = is_array($list->tags ?? null) ? $list->tags : [];
        $newTag = trim((string) $validated['tag']);

        if ($newTag !== '' && !in_array($newTag, $existingTags, true)) {
            $existingTags[] = $newTag;
        }

        $list->update([
            'tags' => $this->normalizeTags($existingTags),
        ]);

        return redirect()
            ->route('customer.lists.index', ['tab' => 'tags'])
            ->with('success', 'Tag added successfully.');
    }

    public function updateTag(Request $request, EmailList $list)
    {
        $this->authorizeOwnership($list);

        $validated = $request->validate([
            'old_tag' => ['required', 'string', 'max:100'],
            'new_tag' => ['required', 'string', 'max:100'],
        ]);

        $tags = is_array($list->tags ?? null) ? $list->tags : [];
        $oldTag = trim((string) $validated['old_tag']);
        $newTag = trim((string) $validated['new_tag']);

        foreach ($tags as $index => $tag) {
            if ((string) $tag === $oldTag) {
                $tags[$index] = $newTag;
            }
        }

        $list->update([
            'tags' => $this->normalizeTags($tags),
        ]);

        return redirect()
            ->route('customer.lists.index', ['tab' => 'tags'])
            ->with('success', 'Tag updated successfully.');
    }

    public function destroyTag(Request $request, EmailList $list)
    {
        $this->authorizeOwnership($list);

        $validated = $request->validate([
            'tag' => ['required', 'string', 'max:100'],
        ]);

        $tagToDelete = trim((string) $validated['tag']);
        $tags = is_array($list->tags ?? null) ? $list->tags : [];
        $tags = array_values(array_filter($tags, static fn ($tag) => (string) $tag !== $tagToDelete));

        $list->update([
            'tags' => $this->normalizeTags($tags),
        ]);

        return redirect()
            ->route('customer.lists.index', ['tab' => 'tags'])
            ->with('success', 'Tag deleted successfully.');
    }

    private function normalizeTags(mixed $tagsInput): array
    {
        $tags = [];

        if (is_string($tagsInput)) {
            $tags = preg_split('/[,\n]+/', $tagsInput) ?: [];
        } elseif (is_array($tagsInput)) {
            $tags = $tagsInput;
        }

        $normalized = array_values(array_unique(array_filter(array_map(
            static fn ($tag) => trim((string) $tag),
            $tags
        ), static fn (string $tag) => $tag !== '')));

        return array_slice($normalized, 0, 50);
    }
}
