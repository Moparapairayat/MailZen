<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\EmailList;
use App\Models\ListSegment;
use Illuminate\Http\Request;

class ListSegmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:lists.permissions.can_access_lists');
    }

    public function create(Request $request)
    {
        $customer = $request->user('customer');

        $lists = EmailList::query()
            ->where('customer_id', $customer->id)
            ->orderBy('display_name')
            ->orderBy('name')
            ->get();

        if ($lists->isEmpty()) {
            return redirect()
                ->route('customer.lists.index', ['tab' => 'segments'])
                ->with('error', 'Please create an email list first.');
        }

        $defaultListIds = old('list_ids');
        if (!is_array($defaultListIds) || count($defaultListIds) === 0) {
            $defaultListIds = [(string) $request->query('list_id', (string) $lists->first()->id)];
        }

        $conditionFieldOptions = [
            'Subscriber Profile Conditions' => [
                ['value' => 'email', 'label' => 'Email'],
                ['value' => 'first_name', 'label' => 'First name'],
                ['value' => 'last_name', 'label' => 'Last name'],
                ['value' => 'tags', 'label' => 'Tags'],
                ['value' => 'custom_fields', 'label' => 'Custom field'],
            ],
            'Subscription Conditions' => [
                ['value' => 'status', 'label' => 'Subscription status'],
                ['value' => 'subscribed_at', 'label' => 'Subscribed date'],
                ['value' => 'unsubscribed_at', 'label' => 'Unsubscribed date'],
                ['value' => 'source', 'label' => 'Source'],
                ['value' => 'confirmed_at', 'label' => 'Confirmed date'],
            ],
            'Engagement Conditions' => [
                ['value' => 'last_opened_at', 'label' => 'Last opened date'],
                ['value' => 'open_count', 'label' => 'Open count'],
                ['value' => 'last_clicked_at', 'label' => 'Last clicked date'],
                ['value' => 'click_count', 'label' => 'Click count'],
                ['value' => 'inactive_days', 'label' => 'Inactive days'],
            ],
            'Campaign Activity' => [
                ['value' => 'campaign_received', 'label' => 'Received campaign'],
                ['value' => 'campaign_opened', 'label' => 'Opened campaign'],
                ['value' => 'campaign_clicked', 'label' => 'Clicked campaign'],
                ['value' => 'campaign_not_opened', 'label' => 'Did not open campaign'],
                ['value' => 'campaign_bounced', 'label' => 'Bounced after campaign'],
            ],
        ];

        $operatorOptions = [
            ['value' => 'is', 'label' => 'is'],
            ['value' => 'is_not', 'label' => 'is not'],
            ['value' => 'contains', 'label' => 'contains'],
            ['value' => 'not_contains', 'label' => 'does not contain'],
            ['value' => 'greater_than', 'label' => 'is greater than'],
            ['value' => 'less_than', 'label' => 'is less than'],
            ['value' => 'between', 'label' => 'is between'],
            ['value' => 'in_last_days', 'label' => 'is in last (days)'],
            ['value' => 'before', 'label' => 'is before'],
            ['value' => 'after', 'label' => 'is after'],
        ];

        return view('customer.lists.segments.create', compact(
            'lists',
            'defaultListIds',
            'conditionFieldOptions',
            'operatorOptions'
        ));
    }

    public function store(Request $request)
    {
        $customer = $request->user('customer');

        $validated = $request->validate([
            'list_ids' => ['required', 'array', 'min:1'],
            'list_ids.*' => ['required', 'integer', 'exists:email_lists,id'],
            'name' => ['required', 'string', 'max:255'],
            'combine_operator' => ['required', 'in:all,any'],
            'conditions' => ['nullable', 'array'],
            'conditions.*.field' => ['nullable', 'string', 'max:100'],
            'conditions.*.operator' => ['nullable', 'string', 'max:50'],
            'conditions.*.value' => ['nullable', 'string', 'max:1000'],
        ]);

        $listIds = collect($validated['list_ids'])
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $ownedListIds = EmailList::query()
            ->where('customer_id', $customer->id)
            ->whereIn('id', $listIds)
            ->pluck('id');

        if ($ownedListIds->count() !== $listIds->count()) {
            return back()
                ->withErrors(['list_ids' => 'One or more selected lists are invalid.'])
                ->withInput();
        }

        $conditions = collect($validated['conditions'] ?? [])
            ->map(function ($condition) {
                return [
                    'field' => trim((string) ($condition['field'] ?? '')),
                    'operator' => trim((string) ($condition['operator'] ?? '')),
                    'value' => trim((string) ($condition['value'] ?? '')),
                ];
            })
            ->filter(fn ($condition) => $condition['field'] !== '' && $condition['operator'] !== '' && $condition['value'] !== '')
            ->values()
            ->all();

        foreach ($ownedListIds as $listId) {
            ListSegment::create([
                'list_id' => (int) $listId,
                'name' => $validated['name'],
                'description' => null,
                'rules' => [
                    'combine_operator' => $validated['combine_operator'],
                    'conditions' => $conditions,
                ],
                'subscribers_count' => 0,
                'is_active' => true,
            ]);
        }

        return redirect()
            ->route('customer.lists.index', ['tab' => 'segments'])
            ->with('success', $ownedListIds->count() > 1 ? 'Segments created successfully.' : 'Segment created successfully.');
    }
}
