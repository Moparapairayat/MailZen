@extends('layouts.admin')

@section('title', __('Dashboard'))
@section('page-title', __('Dashboard'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.dashboard', ['range' => '7d']) }}"
                class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ ($range ?? '7d') === '7d' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}"
            >
                {{ __('Last 7 Days') }}
            </a>
            <a
                href="{{ route('admin.dashboard', ['range' => '30d']) }}"
                class="px-3 py-2 rounded-lg text-sm border border-admin-border hover:bg-white/5 {{ ($range ?? '7d') === '30d' ? 'bg-white/10 text-admin-text-primary' : 'text-admin-text-secondary' }}"
            >
                {{ __('Last 30 Days') }}
            </a>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <input type="hidden" name="range" value="custom" />
            <div class="flex items-center gap-2">
                <div>
                    <label class="sr-only" for="from">{{ __('From') }}</label>
                    <input
                        id="from"
                        name="from"
                        type="date"
                        value="{{ optional($startDate ?? null)->toDateString() }}"
                        class="px-3 py-2 rounded-lg text-sm border border-admin-border bg-white/5 text-admin-text-primary"
                    />
                </div>
                <div>
                    <label class="sr-only" for="to">{{ __('To') }}</label>
                    <input
                        id="to"
                        name="to"
                        type="date"
                        value="{{ optional($endDate ?? null)->toDateString() }}"
                        class="px-3 py-2 rounded-lg text-sm border border-admin-border bg-white/5 text-admin-text-primary"
                    />
                </div>
                <button type="submit" class="px-3 py-2 rounded-lg text-sm bg-primary-500 text-white hover:bg-primary-600">
                    {{ __('Apply') }}
                </button>
            </div>
        </form>
    </div>

    @php
        $rangeLabel = __('Last 7 Days');
        if (($range ?? '7d') === '30d') {
            $rangeLabel = __('Last 30 Days');
        } elseif (($range ?? '') === 'custom') {
            $rangeLabel = __('Custom Range');
        }
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500/10 text-primary-500 shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13 7C13 9.20914 11.2091 11 9 11C6.79086 11 5 9.20914 5 7C5 4.79086 6.79086 3 9 3C11.2091 3 13 4.79086 13 7Z" stroke="#1E5FEA" stroke-width="1.5"/>
                                <path d="M15 11C17.2091 11 19 9.20914 19 7C19 4.79086 17.2091 3 15 3" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M11 14H7C4.23858 14 2 16.2386 2 19C2 20.1046 2.89543 21 4 21H14C15.1046 21 16 20.1046 16 19C16 16.2386 13.7614 14 11 14Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M17 14C19.7614 14 22 16.2386 22 19C22 20.1046 21.1046 21 20 21H18.5" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                        </div>
                        <p class="text-sm text-admin-text-secondary truncate">{{ __('Total User') }}</p>
                    </div>
                </div>
                <p class="text-3xl font-semibold tracking-tight text-admin-text-primary">{{ number_format($usersCount ?? 0) }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-admin-text-secondary bg-white/5 border border-admin-border rounded-md px-2 py-1">{{ $rangeLabel }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-500">
                        0.0%
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7 7 7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18" />
                        </svg>
                    </span>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500/10 text-primary-500 shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 8L9.94202 9.73943C11.6572 10.7535 12.3428 10.7535 14.058 9.73943L17 8" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21.9842 12.9756C22.0053 11.9899 22.0053 11.0101 21.9842 10.0244C21.9189 6.95886 21.8862 5.42609 20.7551 4.29066C19.6239 3.15523 18.0497 3.11568 14.9012 3.03657C12.9607 2.98781 11.0393 2.98781 9.09882 3.03656C5.95033 3.11566 4.37608 3.15521 3.24495 4.29065C2.11382 5.42608 2.08114 6.95885 2.01576 10.0244C1.99474 11.0101 1.99475 11.9899 2.01577 12.9756C2.08114 16.0412 2.11383 17.5739 3.24496 18.7094C4.37608 19.8448 5.95033 19.8843 9.09883 19.9634C10.404 19.9962 11.7005 20.007 13 19.9957" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M18.5 14L18.7579 14.697C19.0961 15.611 19.2652 16.068 19.5986 16.4014C19.932 16.7348 20.389 16.9039 21.303 17.2421L22 17.5L21.303 17.7579C20.389 18.0961 19.932 18.2652 19.5986 18.5986C19.2652 18.932 19.0961 19.389 18.7579 20.303L18.5 21L18.2421 20.303C17.9039 19.389 17.7348 18.932 17.4014 18.5986C17.068 18.2652 16.611 18.0961 15.697 17.7579L15 17.5L15.697 17.2421C16.611 16.9039 17.068 16.7348 17.4014 16.4014C17.7348 16.068 17.9039 15.611 18.2421 14.697L18.5 14Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="text-sm text-admin-text-secondary truncate">{{ __('Subscribers') }}</p>
                    </div>
                </div>
                <p class="text-3xl font-semibold tracking-tight text-admin-text-primary">{{ number_format($subscribersCount ?? 0) }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-admin-text-secondary bg-white/5 border border-admin-border rounded-md px-2 py-1">{{ $rangeLabel }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-500">
                        0.0%
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7 7 7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18" />
                        </svg>
                    </span>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500/10 text-primary-500 shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20.9427 16.8354C20.2864 12.8866 18.2432 9.94613 16.467 8.219C15.9501 7.71642 15.6917 7.46513 15.1208 7.23257C14.5499 7 14.0592 7 13.0778 7H10.9222C9.94081 7 9.4501 7 8.87922 7.23257C8.30834 7.46513 8.04991 7.71642 7.53304 8.219C5.75682 9.94613 3.71361 12.8866 3.05727 16.8354C2.56893 19.7734 5.27927 22 8.30832 22H15.6917C18.7207 22 21.4311 19.7734 20.9427 16.8354Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.25662 4.44287C7.05031 4.14258 6.75128 3.73499 7.36899 3.64205C8.00392 3.54651 8.66321 3.98114 9.30855 3.97221C9.89237 3.96413 10.1898 3.70519 10.5089 3.33548C10.8449 2.94617 11.3652 2 12 2C12.6348 2 13.1551 2.94617 13.4911 3.33548C13.8102 3.70519 14.1076 3.96413 14.6914 3.97221C15.3368 3.98114 15.9961 3.54651 16.631 3.64205C17.2487 3.73499 16.9497 4.14258 16.7434 4.44287L15.8105 5.80064C15.4115 6.38146 15.212 6.67187 14.7944 6.83594C14.3769 7 13.8373 7 12.7582 7H11.2418C10.1627 7 9.6231 7 9.20556 6.83594C8.78802 6.67187 8.5885 6.38146 8.18945 5.80064L7.25662 4.44287Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M13.6267 12.9186C13.4105 12.1205 12.3101 11.4003 10.9892 11.9391C9.66829 12.4778 9.45847 14.2113 11.4565 14.3955C12.3595 14.4787 12.9483 14.2989 13.4873 14.8076C14.0264 15.3162 14.1265 16.7308 12.7485 17.112C11.3705 17.4932 10.006 16.8976 9.85742 16.0517M11.8417 10.9927V11.7531M11.8417 17.2293V17.9927" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="text-sm text-admin-text-secondary truncate">{{ __('Earnings') }}</p>
                    </div>
                </div>
                <p class="text-3xl font-semibold tracking-tight text-admin-text-primary">{{ $currencySymbol }}{{ number_format((float) ($earnings ?? 0), 2) }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-admin-text-secondary bg-white/5 border border-admin-border rounded-md px-2 py-1">{{ $rangeLabel }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-500">
                        0.0%
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7-7-7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21V3" />
                        </svg>
                    </span>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500/10 text-primary-500 shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14.9263 2.91103L8.27352 6.10452C7.76151 6.35029 7.21443 6.41187 6.65675 6.28693C6.29177 6.20517 6.10926 6.16429 5.9623 6.14751C4.13743 5.93912 3 7.38342 3 9.04427V9.95573C3 11.6166 4.13743 13.0609 5.9623 12.8525C6.10926 12.8357 6.29178 12.7948 6.65675 12.7131C7.21443 12.5881 7.76151 12.6497 8.27352 12.8955L14.9263 16.089C16.4534 16.8221 17.217 17.1886 18.0684 16.9029C18.9197 16.6172 19.2119 16.0041 19.7964 14.778C21.4012 11.4112 21.4012 7.58885 19.7964 4.22196C19.2119 2.99586 18.9197 2.38281 18.0684 2.0971C17.217 1.8114 16.4534 2.17794 14.9263 2.91103Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M11.4581 20.7709L9.96674 22C6.60515 19.3339 7.01583 18.0625 7.01583 13H8.14966C8.60978 15.8609 9.69512 17.216 11.1927 18.197C12.1152 18.8012 12.3054 20.0725 11.4581 20.7709Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.5 12.5V6.5" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="text-sm text-admin-text-secondary truncate">{{ __('Campaigns Created') }}</p>
                    </div>
                </div>
                <p class="text-3xl font-semibold tracking-tight text-admin-text-primary">{{ number_format($campaignsCreated ?? 0) }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-admin-text-secondary bg-white/5 border border-admin-border rounded-md px-2 py-1">{{ $rangeLabel }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-500">
                        0.0%
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7-7-7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21V3" />
                        </svg>
                    </span>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500/10 text-primary-500 shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.31802 19.682C3 18.364 3 16.2426 3 12C3 7.75736 3 5.63604 4.31802 4.31802C5.63604 3 7.75736 3 12 3C16.2426 3 18.364 3 19.682 4.31802C21 5.63604 21 7.75736 21 12C21 16.2426 21 18.364 19.682 19.682C18.364 21 16.2426 21 12 21C7.75736 21 5.63604 21 4.31802 19.682Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 12H8.5L10.5 8L13.5 16L15.5 12H18" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="text-sm text-admin-text-secondary truncate">{{ __('Campaigns Ran') }}</p>
                    </div>
                </div>
                <p class="text-3xl font-semibold tracking-tight text-admin-text-primary">{{ number_format($campaignsRan ?? 0) }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-admin-text-secondary bg-white/5 border border-admin-border rounded-md px-2 py-1">{{ $rangeLabel }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-500">
                        0.0%
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7-7-7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21V3" />
                        </svg>
                    </span>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500/10 text-primary-500 shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 9C4.55228 9 5 8.55228 5 8C5 7.44772 4.55228 7 4 7C3.44772 7 3 7.44772 3 8C3 8.55228 3.44772 9 4 9Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 17C4.55228 17 5 16.5523 5 16C5 15.4477 4.55228 15 4 15C3.44772 15 3 15.4477 3 16C3 16.5523 3.44772 17 4 17Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 21V3" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.91421 9.41421L9.35355 8.85355C9.29393 8.79393 9.26412 8.76412 9.23778 8.73311C9.10232 8.5736 9.02031 8.37561 9.00331 8.16703C9 8.12647 9 8.08432 9 8C9 7.91568 9 7.87353 9.00331 7.83297C9.02031 7.62439 9.10232 7.4264 9.23778 7.26689C9.26412 7.23588 9.29393 7.20607 9.35355 7.14645L9.91421 6.58579C10.2032 6.29676 10.3478 6.15224 10.5315 6.07612C10.7153 6 10.9197 6 11.3284 6H17C17.9428 6 18.4142 6 18.7071 6.29289C19 6.58579 19 7.05719 19 8C19 8.94281 19 9.41421 18.7071 9.70711C18.4142 10 17.9428 10 17 10H11.3284C10.9197 10 10.7153 10 10.5315 9.92388C10.3478 9.84776 10.2032 9.70324 9.91421 9.41421Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.91421 17.4142L9.35355 16.8536C9.29393 16.7939 9.26412 16.7641 9.23778 16.7331C9.10232 16.5736 9.02031 16.3756 9.00331 16.167C9 16.1265 9 16.0843 9 16C9 15.9157 9 15.8735 9.00331 15.833C9.02031 15.6244 9.10232 15.4264 9.23778 15.2669C9.26412 15.2359 9.29393 15.2061 9.35355 15.1464L9.91421 14.5858C10.2032 14.2968 10.3478 14.1522 10.5315 14.0761C10.7153 14 10.9197 14 11.3284 14H19C19.9428 14 20.4142 14 20.7071 14.2929C21 14.5858 21 15.0572 21 16C21 16.9428 21 17.4142 20.7071 17.7071C20.4142 18 19.9428 18 19 18H11.3284C10.9197 18 10.7153 18 10.5315 17.9239C10.3478 17.8478 10.2032 17.7032 9.91421 17.4142Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                        </div>
                        <p class="text-sm text-admin-text-secondary truncate">{{ __('Email Lists Created') }}</p>
                    </div>
                </div>
                <p class="text-3xl font-semibold tracking-tight text-admin-text-primary">{{ number_format($listsCreated ?? 0) }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-admin-text-secondary bg-white/5 border border-admin-border rounded-md px-2 py-1">{{ $rangeLabel }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-500">
                        0.0%
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7-7-7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21V3" />
                        </svg>
                    </span>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500/10 text-primary-500 shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.0156 5C15.0156 3.58579 15.0156 2.87868 15.5272 2.43934C16.0387 2 16.8621 2 18.5089 2C20.1556 2 20.979 2 21.4905 2.43934C22.0021 2.87868 22.0021 3.58579 22.0021 5C22.0021 6.41421 22.0021 7.12132 21.4905 7.56066C20.979 8 20.1556 8 18.5089 8C16.8621 8 16.0387 8 15.5272 7.56066C15.0156 7.12132 15.0156 6.41421 15.0156 5Z" stroke="#1E5FEA" stroke-width="1.5"/>
                                <path d="M15.0156 19C15.0156 17.5858 15.0156 16.8787 15.5272 16.4393C16.0387 16 16.8621 16 18.5089 16C20.1556 16 20.979 16 21.4905 16.4393C22.0021 16.8787 22.0021 17.5858 22.0021 19C22.0021 20.4142 22.0021 21.1213 21.4905 21.5607C20.979 22 20.1556 22 18.5089 22C16.8621 22 16.0387 22 15.5272 21.5607C15.0156 21.1213 15.0156 20.4142 15.0156 19Z" stroke="#1E5FEA" stroke-width="1.5"/>
                                <path d="M8.54128 10.4825L5.52344 13.4949M8.54128 13.4949L5.52344 10.4825" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.04524 17.0314C9.81002 17.0314 12.0102 14.7709 12.0102 11.9931C12.0102 9.21534 9.76889 6.9635 7.00412 6.9635M7.04524 17.0314C4.28047 17.0314 1.99805 14.7709 1.99805 11.9931C1.99805 9.21534 4.23934 6.9635 7.00412 6.9635M7.04524 17.0314C6.97561 19.1612 8.53173 19.9388 9.63979 19.9795H12.0102M7.00412 6.9635C6.93377 4.80343 8.51699 4.04384 9.6398 3.99609H12.0209" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="text-sm text-admin-text-secondary truncate">{{ __('Subscription Cancelled') }}</p>
                    </div>
                </div>
                <p class="text-3xl font-semibold tracking-tight text-admin-text-primary">{{ number_format($subscriptionsCancelled ?? 0) }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-admin-text-secondary bg-white/5 border border-admin-border rounded-md px-2 py-1">{{ $rangeLabel }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-500">
                        0.0%
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7-7-7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21V3" />
                        </svg>
                    </span>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500/10 text-primary-500 shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20.9977 13C21 12.5299 21 12.0307 21 11.5C21 7.02166 21 4.78249 19.6088 3.39124C18.2175 2 15.9783 2 11.5 2C7.02166 2 4.78249 2 3.39124 3.39124C2 4.78249 2 7.02166 2 11.5C2 15.9783 2 18.2175 3.39124 19.6088C4.78249 21 7.02166 21 11.5 21C12.0307 21 12.5299 21 13 20.9977" stroke="#1E5FEA" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M18.5 15L18.7579 15.697C19.0961 16.611 19.2652 17.068 19.5986 17.4014C19.932 17.7348 20.389 17.9039 21.303 18.2421L22 18.5L21.303 18.7579C20.389 19.0961 19.932 19.2652 19.5986 19.5986C19.2652 19.932 19.0961 20.389 18.7579 21.303L18.5 22L18.2421 21.303C17.9039 20.389 17.7348 19.932 17.4014 19.5986C17.068 19.2652 16.611 19.0961 15.697 18.7579L15 18.5L15.697 18.2421C16.611 17.9039 17.068 17.7348 17.4014 17.4014C17.7348 17.068 17.9039 16.611 18.2421 15.697L18.5 15Z" stroke="#1E5FEA" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M2 9H21" stroke="#1E5FEA" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M6.49976 5.5H6.50874" stroke="#1E5FEA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10.4998 5.5H10.5088" stroke="#1E5FEA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="text-sm text-admin-text-secondary truncate">{{ __('Templates Created') }}</p>
                    </div>
                </div>
                <p class="text-3xl font-semibold tracking-tight text-admin-text-primary">{{ number_format($templatesCreated ?? 0) }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-admin-text-secondary bg-white/5 border border-admin-border rounded-md px-2 py-1">{{ $rangeLabel }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-rose-500">
                        0.0%
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7-7-7" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21V3" />
                        </svg>
                    </span>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card :title="__('Earnings Data Chart')" :padding="false">
            <div class="px-6 py-4">
                <div class="w-full" style="height: 280px;">
                    <canvas id="earningsChart"></canvas>
                </div>
            </div>
        </x-card>

        @php
            $showAllUrl = route('admin.activities.index', request()->only(['range', 'from', 'to']));
        @endphp
        <x-card :padding="false">
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-admin-text-primary">{{ __('Recent Activity') }}</h3>
                    </div>
                    <a href="{{ $showAllUrl }}" class="text-sm text-primary-500 hover:text-primary-600">
                        {{ __('Show all activities') }}
                    </a>
                </div>
            </x-slot>

            <div class="divide-y divide-admin-border/10">
                @forelse(($recentActivity ?? []) as $item)
                    <div class="px-6 py-3 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-admin-text-primary">{{ $item['label'] ?? '' }}</p>
                            <p class="text-xs text-admin-text-secondary truncate">{{ $item['detail'] ?? '' }}</p>
                        </div>
                        <p class="text-xs text-admin-text-secondary whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($item['at'])->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-admin-text-secondary">
                        <p>{{ __('No recent activity') }}</p>
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>

    <x-card :title="__('World Visitors')" :padding="false">
        <div class="px-6 py-4">
            <div class="relative">
                <div id="worldVisitorsMap" style="height: 360px;"></div>
                @if(empty($worldVisitors ?? []))
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <p class="text-sm text-admin-text-secondary bg-admin-sidebar/80 border border-admin-border rounded-lg px-3 py-2">
                            {{ __('No visitor data yet') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"></script>
    <script>
        (function () {
            const labels = @json($earningsChartLabels ?? []);
            const values = @json($earningsChartValues ?? []);

            const destroyChart = () => {
                if (window.__earningsChart && typeof window.__earningsChart.destroy === 'function') {
                    window.__earningsChart.destroy();
                }
                window.__earningsChart = null;

                if (window.__earningsChartThemeObserver && typeof window.__earningsChartThemeObserver.disconnect === 'function') {
                    window.__earningsChartThemeObserver.disconnect();
                }
                window.__earningsChartThemeObserver = null;
            };

            const initChart = () => {
                destroyChart();

                const el = document.getElementById('earningsChart');
                if (!el) return;

                // Check if Chart.js is loaded, if not, retry after a short delay
                if (typeof Chart === 'undefined') {
                    setTimeout(initChart, 100);
                    return;
                }

                const ctx = el.getContext('2d');
                if (!ctx) return;

                const isDark = () => document.documentElement.classList.contains('dark');
                const theme = () => {
                    if (isDark()) {
                        return {
                            gridColor: 'rgba(255, 255, 255, 0.10)',
                            tickColor: 'rgba(255, 255, 255, 0.70)',
                            pointBorder: 'rgba(0,0,0,0.0)',
                        };
                    }
                    return {
                        gridColor: 'rgba(17, 24, 39, 0.10)',
                        tickColor: 'rgba(17, 24, 39, 0.70)',
                        pointBorder: 'rgba(255,255,255,0.95)',
                    };
                };

                const t0 = theme();
                window.__earningsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: @json(__('Earnings')),
                            data: values,
                            borderColor: '#1E5FEA',
                            backgroundColor: 'rgba(30, 95, 234, 0.15)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#1E5FEA',
                            pointBorderColor: t0.pointBorder,
                            pointBorderWidth: 2,
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const v = Number(ctx.raw || 0);
                                        return ' {{ $currencySymbol }}' + v.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: t0.gridColor },
                                ticks: { color: t0.tickColor }
                            },
                            y: {
                                grid: { color: t0.gridColor },
                                ticks: { color: t0.tickColor }
                            }
                        }
                    }
                });

                const applyTheme = () => {
                    const t = theme();
                    const c = window.__earningsChart;
                    if (!c) return;
                    c.options.scales.x.grid.color = t.gridColor;
                    c.options.scales.y.grid.color = t.gridColor;
                    c.options.scales.x.ticks.color = t.tickColor;
                    c.options.scales.y.ticks.color = t.tickColor;
                    if (c.data.datasets && c.data.datasets[0]) {
                        c.data.datasets[0].pointBorderColor = t.pointBorder;
                    }
                    c.update('none');
                };

                window.__earningsChartThemeObserver = new MutationObserver(applyTheme);
                window.__earningsChartThemeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            };

            document.addEventListener('turbo:before-cache', destroyChart);
            document.addEventListener('turbo:load', initChart);
            if (document.readyState !== 'loading') {
                initChart();
            }
        })();
    </script>

    <script>
        (function () {
            const visitors = @json($worldVisitors ?? []);
            const defaultVisitorLabel = @json(__('Visitor'));

            const destroyMap = () => {
                if (window.__worldMap && typeof window.__worldMap.destroy === 'function') {
                    window.__worldMap.destroy();
                }
                window.__worldMap = null;

                if (window.__worldMapThemeObserver && typeof window.__worldMapThemeObserver.disconnect === 'function') {
                    window.__worldMapThemeObserver.disconnect();
                }
                window.__worldMapThemeObserver = null;
            };

            const initMap = () => {
                destroyMap();

                const mapEl = document.getElementById('worldVisitorsMap');
                if (!mapEl) return;

                // Check if jsVectorMap is loaded, if not, retry after a short delay
                if (typeof jsVectorMap === 'undefined') {
                    setTimeout(initMap, 100);
                    return;
                }

                const isDark = () => document.documentElement.classList.contains('dark');
                const buildMarkers = () => {
                    if (!Array.isArray(visitors)) return [];
                    return visitors
                        .filter(v => v && v.lat != null && v.lng != null)
                        .map(v => ({
                            name: v.label || v.country || defaultVisitorLabel,
                            coords: [Number(v.lat), Number(v.lng)],
                            visitors: Number(v.visitors || 0),
                        }));
                };

                const t = () => {
                    if (isDark()) {
                        return {
                            regionFill: '#1b1b20',
                            regionStroke: 'rgba(255,255,255,0.10)',
                            markerFill: '#109489',
                            markerStroke: 'rgba(255,255,255,0.75)',
                        };
                    }
                    return {
                        regionFill: '#f8fafc',
                        regionStroke: 'rgba(17,24,39,0.12)',
                        markerFill: '#109489',
                        markerStroke: 'rgba(17,24,39,0.20)',
                    };
                };

                window.__worldMap = new jsVectorMap({
                    selector: '#worldVisitorsMap',
                    map: 'world',
                    zoomButtons: true,
                    markers: buildMarkers(),
                    markerStyle: {
                        initial: {
                            fill: t().markerFill,
                            stroke: t().markerStroke,
                            r: 5,
                        },
                        hover: {
                            r: 7,
                        }
                    },
                    regionStyle: {
                        initial: {
                            fill: t().regionFill,
                            stroke: t().regionStroke,
                            strokeWidth: 1,
                        },
                        hover: {
                            fill: '#e2e8f0',
                        }
                    },
                    onMarkerTooltipShow: function (tooltip, index) {
                        const marker = (window.__worldMap && window.__worldMap.markers && window.__worldMap.markers[index]) ? window.__worldMap.markers[index] : null;
                        if (!marker) return;
                        const data = marker.config || {};
                        const label = data.name || defaultVisitorLabel;
                        const count = data.visitors != null ? data.visitors : '';
                        tooltip.text(label + (count !== '' ? (' (' + count + ')') : ''));
                    },
                });

                const applyTheme = () => {
                    const map = window.__worldMap;
                    if (!map) return;
                    const tt = t();
                    map.setOptions({
                        regionStyle: {
                            initial: {
                                fill: tt.regionFill,
                                stroke: tt.regionStroke,
                                strokeWidth: 1,
                            }
                        },
                        markerStyle: {
                            initial: {
                                fill: tt.markerFill,
                                stroke: tt.markerStroke,
                                r: 5,
                            }
                        }
                    });
                };

                window.__worldMapThemeObserver = new MutationObserver(applyTheme);
                window.__worldMapThemeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            };

            document.addEventListener('turbo:before-cache', destroyMap);
            document.addEventListener('turbo:load', initMap);
            if (document.readyState !== 'loading') {
                initMap();
            }
        })();
    </script>
@endpush

