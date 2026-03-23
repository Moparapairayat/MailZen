@extends('layouts.customer')

@section('title', 'Email Lists')
@section('page-title', 'Email Lists')

@section('content')
<div class="space-y-6">
    @php
        $activeTab = isset($tab) ? (string) $tab : (string) request()->input('tab', 'lists');
        if ($activeTab === '') {
            $activeTab = 'lists';
        }
    @endphp

    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex flex-wrap gap-x-6" aria-label="Tabs">
            <a href="{{ route('customer.lists.index', ['tab' => 'overview']) }}" class="whitespace-nowrap py-3 px-1 text-sm font-medium" @class([
                'border-b-2 border-primary-500 text-primary-600' => $activeTab === 'overview',
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== 'overview',
            ])>
                Overview
            </a>
            <a href="{{ route('customer.lists.index') }}" class="whitespace-nowrap py-3 px-1 text-sm font-medium" @class([
                'border-b-2 border-primary-500 text-primary-600' => $activeTab === 'lists',
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== 'lists',
            ])>
                Lists
            </a>
            <a href="{{ route('customer.lists.index', ['tab' => 'contacts']) }}" class="whitespace-nowrap py-3 px-1 text-sm font-medium" @class([
                'border-b-2 border-primary-500 text-primary-600' => $activeTab === 'contacts',
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== 'contacts',
            ])>
                Contacts
            </a>
            <a href="{{ route('customer.lists.index', ['tab' => 'segments']) }}" class="whitespace-nowrap py-3 px-1 text-sm font-medium" @class([
                'border-b-2 border-primary-500 text-primary-600' => $activeTab === 'segments',
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== 'segments',
            ])>
                Segments
            </a>
            <a href="{{ route('customer.lists.index', ['tab' => 'tags']) }}" class="whitespace-nowrap py-3 px-1 text-sm font-medium" @class([
                'border-b-2 border-primary-500 text-primary-600' => $activeTab === 'tags',
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== 'tags',
            ])>
                Tags
            </a>
            <a href="{{ route('customer.forms.index') }}" class="whitespace-nowrap py-3 px-1 text-sm font-medium border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Forms & Popups
            </a>
        </nav>
    </div>

    @if($activeTab === 'overview')
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
            <x-card>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Lists</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((int) ($overview->lists_count ?? 0)) }}</div>
            </x-card>
            <x-card>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscribers</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((int) ($overview->subscribers_count ?? 0)) }}</div>
            </x-card>
            <x-card>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Confirmed</div>
                <div class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format((int) ($overview->confirmed_subscribers_count ?? 0)) }}</div>
            </x-card>
            <x-card>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Unsubscribed</div>
                <div class="mt-2 text-3xl font-bold text-gray-600 dark:text-gray-400">{{ number_format((int) ($overview->unsubscribed_count ?? 0)) }}</div>
            </x-card>
            <x-card>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Bounced</div>
                <div class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ number_format((int) ($overview->bounced_count ?? 0)) }}</div>
            </x-card>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <x-card>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Campaigns</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((int) ($overviewCampaign->campaigns_count ?? 0)) }}</div>
            </x-card>
            <x-card>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivered</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((int) ($overviewCampaign->delivered_count ?? 0)) }}</div>
            </x-card>
            <x-card>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Rate</div>
                <div class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format((float) ($overviewCampaign->open_rate ?? 0), 2) }}%</div>
            </x-card>
            <x-card>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Click Rate</div>
                <div class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format((float) ($overviewCampaign->click_rate ?? 0), 2) }}%</div>
            </x-card>
        </div>

        <x-card>
            <div class="flex flex-col gap-2">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">List health</div>
                @php
                    $total = (int) ($overview->subscribers_count ?? 0);
                    $confirmed = (int) ($overview->confirmed_subscribers_count ?? 0);
                    $unsub = (int) ($overview->unsubscribed_count ?? 0);
                    $bounced = (int) ($overview->bounced_count ?? 0);
                    $confirmedPct = $total > 0 ? min(100, max(0, round(($confirmed / $total) * 100))) : 0;
                    $unsubPct = $total > 0 ? min(100, max(0, round(($unsub / $total) * 100))) : 0;
                    $bouncedPct = $total > 0 ? min(100, max(0, round(($bounced / $total) * 100))) : 0;
                @endphp
                <div class="space-y-3">
                    <div>
                        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Confirmed</span>
                            <span>{{ $confirmedPct }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $confirmedPct }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Unsubscribed</span>
                            <span>{{ $unsubPct }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-gray-600 h-2 rounded-full" style="width: {{ $unsubPct }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Bounced</span>
                            <span>{{ $bouncedPct }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-red-600 h-2 rounded-full" style="width: {{ $bouncedPct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>
    @elseif($activeTab === 'forms')
        <x-card>
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Forms & Popups are managed globally.
            </div>
            <div class="mt-4">
                <a href="{{ route('customer.forms.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                    Go to Forms
                </a>
            </div>
        </x-card>
    @elseif($activeTab === 'contacts')
        <x-card>
            <div class="text-sm text-gray-700 dark:text-gray-300">
                This area is organized per list. Choose a list below to manage its contacts.
            </div>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($emailLists as $list)
                    <a href="{{ route('customer.lists.subscribers.index', $list) }}" class="block rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $list->display_name ?? $list->name }}</div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format((int) ($list->subscribers_count ?? 0)) }} subscribers</div>
                    </a>
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        No email lists yet.
                    </div>
                @endforelse
            </div>
        </x-card>

        @if($emailLists->hasPages())
            <div>
                {{ $emailLists->withQueryString()->links() }}
            </div>
        @endif
    @elseif($activeTab === 'segments')
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" action="{{ route('customer.lists.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <input type="hidden" name="tab" value="segments">
                <label for="segment_sort" class="text-sm text-gray-600 dark:text-gray-400">Sort by</label>
                <select
                    id="segment_sort"
                    name="segment_sort"
                    class="w-full sm:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                >
                    <option value="created_at_desc" {{ $segmentSort === 'created_at_desc' ? 'selected' : '' }}>Created at</option>
                    <option value="created_at_asc" {{ $segmentSort === 'created_at_asc' ? 'selected' : '' }}>Created at (oldest)</option>
                    <option value="name_asc" {{ $segmentSort === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ $segmentSort === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    <option value="subscribers_desc" {{ $segmentSort === 'subscribers_desc' ? 'selected' : '' }}>Most subscribers</option>
                </select>
                <input
                    type="text"
                    name="segment_search"
                    value="{{ $segmentSearch }}"
                    placeholder="Type to search"
                    class="w-full sm:w-60 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                >
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Apply
                </button>
            </form>

            <a href="{{ route('customer.segments.create') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium bg-gray-700 text-white rounded-md hover:bg-gray-800">
                + Create segment
            </a>
        </div>

        @if($segments && $segments->count() > 0)
            <x-card :padding="false" class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Segment</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">List</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subscribers</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($segments as $segment)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $segment->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $segment->emailList?->display_name ?? $segment->emailList?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ number_format((int) ($segment->subscribers_count ?? 0)) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ optional($segment->created_at)->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @else
            <x-card>
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="5" y="5" width="14" height="6" rx="1.5" stroke-width="1.75"></rect>
                        <rect x="5" y="13" width="14" height="6" rx="1.5" stroke-width="1.75"></rect>
                    </svg>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">You have no segment</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">No segment yet.</p>
                </div>
            </x-card>
        @endif

        @if($segments && $segments->hasPages())
            <div>
                {{ $segments->withQueryString()->links() }}
            </div>
        @endif
    @elseif($activeTab === 'tags')
        <x-card>
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Manage tags per list. These tags can be reused in subscriber edit screens.
            </div>
        </x-card>

        <x-card :padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">List</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Existing Tags</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Add Tag</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($emailLists as $list)
                            <tr>
                                <td class="px-4 py-4 align-top">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $list->display_name ?? $list->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ number_format((int) ($list->subscribers_count ?? 0)) }} subscribers</div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    @if(is_array($list->tags ?? null) && count($list->tags) > 0)
                                        <div class="space-y-2">
                                            @foreach($list->tags as $tag)
                                                <div class="flex items-center gap-2">
                                                    <form method="POST" action="{{ route('customer.lists.tags.update', $list) }}" class="flex items-center gap-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="old_tag" value="{{ $tag }}">
                                                        <input
                                                            type="text"
                                                            name="new_tag"
                                                            value="{{ $tag }}"
                                                            class="w-40 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-xs"
                                                        >
                                                        <button type="submit" class="px-2 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-200">Save</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('customer.lists.tags.destroy', $list) }}" onsubmit="return confirm('Delete this tag?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="tag" value="{{ $tag }}">
                                                        <button type="submit" class="px-2 py-1 text-xs font-medium rounded-md bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-200">Delete</button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">No tags</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <form method="POST" action="{{ route('customer.lists.tags.store', $list) }}" class="flex items-center gap-2">
                                        @csrf
                                        <input
                                            type="text"
                                            name="tag"
                                            placeholder="New tag"
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                        >
                                        <button type="submit" class="px-3 py-2 text-xs font-medium rounded-md bg-primary-600 text-white hover:bg-primary-700">Add</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No email lists found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        @if($emailLists->hasPages())
            <div>
                {{ $emailLists->withQueryString()->links() }}
            </div>
        @endif
    @else
    <!-- Header Actions -->
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between lg:gap-0">
        <div class="w-full lg:flex-1 lg:max-w-lg">
            <form method="GET" action="{{ route('customer.lists.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search lists..."
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                <select
                    name="status"
                    class="w-full lg:w-auto rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <button type="submit" class="w-full lg:w-auto px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    Search
                </button>
            </form>
        </div>
        @customercan('lists.permissions.can_create_lists')
            <a href="{{ route('customer.lists.create') }}" class="inline-flex items-center justify-center w-full lg:w-auto px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create List
            </a>
        @endcustomercan
    </div>

    <x-card :padding="false" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">List</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subscribers</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Campaigns</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Delivered</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Open Rate</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Click Rate</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tags</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Confirmed</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unsubscribed</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bounced</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last subscriber</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($emailLists as $list)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3">
                                <a href="{{ route('customer.lists.show', $list) }}" class="font-medium text-gray-900 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400">
                                    {{ $list->display_name ?? $list->name }}
                                </a>
                                @if($list->description)
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ Str::limit($list->description, 80) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((int) ($list->subscribers_count ?? 0)) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((int) ($list->campaigns_count ?? 0)) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((int) ($list->delivered_count ?? 0)) }}</td>
                            <td class="px-4 py-3 text-sm text-blue-600 dark:text-blue-400">{{ number_format((float) ($list->open_rate ?? 0), 2) }}%</td>
                            <td class="px-4 py-3 text-sm text-indigo-600 dark:text-indigo-400">{{ number_format((float) ($list->click_rate ?? 0), 2) }}%</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                @if(is_array($list->tags ?? null) && count($list->tags) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($list->tags, 0, 3) as $tag)
                                            <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-700 dark:text-gray-200">{{ $tag }}</span>
                                        @endforeach
                                        @if(count($list->tags) > 3)
                                            <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-700 dark:text-gray-200">+{{ count($list->tags) - 3 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((int) ($list->confirmed_subscribers_count ?? 0)) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((int) ($list->unsubscribed_count ?? 0)) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ number_format((int) ($list->bounced_count ?? 0)) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $list->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $list->status === 'inactive' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                    {{ $list->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                ">
                                    {{ ucfirst($list->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $list->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $list->last_subscriber_at ? $list->last_subscriber_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('customer.lists.show', $list) }}" variant="table" size="action" :pill="true" class="p-2" title="View" aria-label="View"><x-lucide name="eye" class="h-4 w-4" /><span class="sr-only">View</span></x-button>
                                    <x-button href="{{ route('customer.lists.subscribers.index', $list) }}" variant="table" size="action" :pill="true" class="p-2" title="Subscribers" aria-label="Subscribers"><x-lucide name="users" class="h-4 w-4" /><span class="sr-only">Subscribers</span></x-button>
                                    <x-button href="{{ route('customer.lists.forms.index', $list) }}" variant="table" size="action" :pill="true" class="p-2" title="Forms" aria-label="Forms"><span class="text-xs font-semibold">F</span><span class="sr-only">Forms</span></x-button>
                                    @customercan('lists.permissions.can_edit_lists')
                                        <x-button href="{{ route('customer.lists.edit', $list) }}" variant="table" size="action" :pill="true" class="p-2" title="Edit" aria-label="Edit"><x-lucide name="pencil" class="h-4 w-4" /><span class="sr-only">Edit</span></x-button>
                                    @endcustomercan
                                    @customercan('lists.permissions.can_delete_lists')
                                        <form method="POST" action="{{ route('customer.lists.destroy', $list) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this list?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="table-danger" size="action" :pill="true" class="p-2" title="Delete" aria-label="Delete"><x-lucide name="trash-2" class="h-4 w-4" /><span class="sr-only">Delete</span></x-button>
                                        </form>
                                    @endcustomercan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-4 py-10">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No email lists</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new email list.</p>
                                    <div class="mt-6">
                                        @customercan('lists.permissions.can_create_lists')
                                            <a href="{{ route('customer.lists.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Create List
                                            </a>
                                        @endcustomercan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @if($emailLists->hasPages())
        <div>
            {{ $emailLists->withQueryString()->links() }}
        </div>
    @endif
    @endif
</div>
@endsection

