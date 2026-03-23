@extends('layouts.public')

@section('title', 'Pricing')

@section('content')
@php
    try {
        $heroTitle = (string) \App\Models\Setting::get('pricing_section_title', (string) \App\Models\Setting::get('pricing_hero_title', 'Simple, transparent pricing'));
        $heroSubtitle = (string) \App\Models\Setting::get('pricing_section_subtitle', (string) \App\Models\Setting::get('pricing_hero_subtitle', "Choose the plan that's right for your business. All plans include a 14-day free trial."));
        $popularBadge = (string) \App\Models\Setting::get('pricing_section_popular_badge', (string) \App\Models\Setting::get('pricing_popular_badge', 'Most Popular'));
        $ctaAuth = (string) \App\Models\Setting::get('pricing_cta_auth', 'Get Started');
        $ctaGuest = (string) \App\Models\Setting::get('pricing_cta_guest', 'Start Free Trial');
        $faqTitle = (string) \App\Models\Setting::get('pricing_faq_title', 'Frequently asked questions');
    } catch (\Throwable $e) {
        $heroTitle = 'Simple, transparent pricing';
        $heroSubtitle = "Choose the plan that's right for your business. All plans include a 14-day free trial.";
        $popularBadge = 'Most Popular';
        $ctaAuth = 'Get Started';
        $ctaGuest = 'Start Free Trial';
        $faqTitle = 'Frequently asked questions';
    }

    $heroTitle = is_string($heroTitle) && trim($heroTitle) !== '' ? $heroTitle : 'Simple, transparent pricing';
    $heroSubtitle = is_string($heroSubtitle) && trim($heroSubtitle) !== '' ? $heroSubtitle : "Choose the plan that's right for your business. All plans include a 14-day free trial.";
    $popularBadge = is_string($popularBadge) && trim($popularBadge) !== '' ? $popularBadge : 'Most Popular';
    $ctaAuth = is_string($ctaAuth) && trim($ctaAuth) !== '' ? $ctaAuth : 'Get Started';
    $ctaGuest = is_string($ctaGuest) && trim($ctaGuest) !== '' ? $ctaGuest : 'Start Free Trial';
    $faqTitle = is_string($faqTitle) && trim($faqTitle) !== '' ? $faqTitle : 'Frequently asked questions';

    try {
        $featuredPlanId = (int) \App\Models\Setting::get('pricing_featured_plan_id', 0);
    } catch (\Throwable $e) {
        $featuredPlanId = 0;
    }

    if (isset($plans) && $plans instanceof \Illuminate\Support\Collection) {
        $popularPlan = $plans->firstWhere('is_popular', true);
        if ($popularPlan) {
            $featuredPlanId = (int) $popularPlan->id;
        }
    }

    if ($featuredPlanId <= 0 && isset($plans) && $plans instanceof \Illuminate\Support\Collection) {
        $featuredPlanId = (int) optional($plans->first())->id;
    }

    $faqRows = isset($faq) && is_array($faq) ? $faq : [
        [
            'q' => 'Can I change plans later?',
            'a' => 'Yes, you can upgrade or downgrade your plan at any time. Changes will be prorated and reflected in your next billing cycle.',
        ],
        [
            'q' => 'What payment methods do you accept?',
            'a' => 'We accept all major credit cards, PayPal, and bank transfers for annual plans.',
        ],
        [
            'q' => 'Is there a free trial?',
            'a' => 'Yes, all plans include a 14-day free trial. No credit card required to start.',
        ],
        [
            'q' => 'What happens if I exceed my plan limits?',
            'a' => "We'll notify you when you're approaching your limits. You can upgrade your plan or purchase additional credits as needed.",
        ],
    ];

    $pricingPlansMonthly = isset($plans) && $plans instanceof \Illuminate\Support\Collection
        ? $plans->where('billing_cycle', 'monthly')->values()
        : collect();
    $pricingPlansYearly = isset($plans) && $plans instanceof \Illuminate\Support\Collection
        ? $plans->where('billing_cycle', 'yearly')->values()
        : collect();

    $pricingAnnualDefault = $pricingPlansMonthly->count() === 0 && $pricingPlansYearly->count() > 0;

    try {
        $pricingForceShowAll = (bool) \App\Models\Setting::get('pricing_section_show_all', false);
        $pricingColumns = (int) \App\Models\Setting::get('pricing_section_columns', 3);
    } catch (\Throwable $e) {
        $pricingForceShowAll = false;
        $pricingColumns = 3;
    }

    if ($pricingColumns < 1) {
        $pricingColumns = 1;
    }
    if ($pricingColumns > 5) {
        $pricingColumns = 5;
    }

    try {
        $pricingToggleMonthly = (string) \App\Models\Setting::get('pricing_section_toggle_monthly', 'Pay Monthly');
        $pricingToggleAnnual = (string) \App\Models\Setting::get('pricing_section_toggle_annual', 'Pay Annually');
        $pricingToggleSave = (string) \App\Models\Setting::get('pricing_section_toggle_save', '(save 20%)');
    } catch (\Throwable $e) {
        $pricingToggleMonthly = 'Pay Monthly';
        $pricingToggleAnnual = 'Pay Annually';
        $pricingToggleSave = '(save 20%)';
    }

    $showBillingToggle = !$pricingForceShowAll && $pricingPlansMonthly->count() > 0 && $pricingPlansYearly->count() > 0;

    $gridClassForColumns = function (int $columns): string {
        if ($columns <= 1) {
            return 'grid-cols-1 gap-8';
        }
        if ($columns === 2) {
            return 'grid-cols-1 gap-8 sm:grid-cols-2';
        }
        if ($columns === 3) {
            return 'grid-cols-1 gap-8 lg:grid-cols-3';
        }
        if ($columns === 4) {
            return 'grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4';
        }
        return 'grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-5';
    };

    $gridClass = $gridClassForColumns($pricingColumns);
@endphp
<div class="bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl">
                {{ $heroTitle }}
            </h1>
            <p class="mt-4 text-xl text-gray-500 dark:text-gray-400">
                {{ $heroSubtitle }}
            </p>
        </div>

        <div class="mt-10" x-data="{ annual: @js($pricingAnnualDefault) }">
            @if($showBillingToggle)
                <div class="flex items-center justify-center gap-4 mb-12">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $pricingToggleMonthly }}</span>
                    <button type="button" @click="annual = !annual" class="relative w-14 h-7 bg-primary-600 rounded-full transition-colors">
                        <span class="absolute top-1 left-1 w-5 h-5 bg-white rounded-full transition-transform" :class="{ 'translate-x-7': annual }"></span>
                    </button>
                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ $pricingToggleAnnual }} <span class="text-primary-500">{{ $pricingToggleSave }}</span></span>
                </div>
            @endif

            <div class="mt-20 grid {{ $gridClass }}" @if($pricingForceShowAll) x-show="true" @else x-show="!annual" @endif>
            @foreach(($pricingForceShowAll ? $pricingPlansMonthly->concat($pricingPlansYearly) : $pricingPlansMonthly) as $plan)
                @php
                    $hasPopular = $plans instanceof \Illuminate\Support\Collection && $plans->where('is_popular', true)->count() > 0;
                    $isFeatured = $hasPopular ? (($plan->is_popular ?? false) === true) : (((int) $plan->id) === (int) $featuredPlanId);
                    $cycle = $plan->billing_cycle === 'yearly' ? 'year' : 'month';

                    $quota = $plan->customerGroup?->limit('sending_quota.monthly_quota', 0);
                    $maxSubscribers = $plan->customerGroup?->limit('lists.limits.max_subscribers', 0);
                    $maxCampaigns = $plan->customerGroup?->limit('campaigns.limits.max_campaigns', 0);

                    $quotaLabel = $quota !== null ? number_format((float) $quota) : '—';
                    $subsLabel = $maxSubscribers !== null ? number_format((int) $maxSubscribers) : '—';
                    $campaignsLabel = $maxCampaigns !== null ? number_format((int) $maxCampaigns) : '—';

                    $featuresView = [
                        'type' => 'legacy',
                        'pros' => [],
                        'cons' => [],
                        'legacyItems' => [],
                    ];

                    $planFeatures = $plan->features ?? null;
                    if (is_array($planFeatures) && (array_key_exists('pros', $planFeatures) || array_key_exists('cons', $planFeatures))) {
                        $featuresView['type'] = 'proscons';
                        $featuresView['pros'] = is_array($planFeatures['pros'] ?? null) ? $planFeatures['pros'] : [];
                        $featuresView['cons'] = is_array($planFeatures['cons'] ?? null) ? $planFeatures['cons'] : [];
                    } elseif (is_array($planFeatures) && count($planFeatures) > 0) {
                        $featuresView['type'] = 'proscons';
                        $featuresView['pros'] = $planFeatures;
                        $featuresView['cons'] = [];
                    } else {
                        $features = $plan->customerGroup?->displayAccessAndLimits() ?? [];
                        foreach ($features as $row) {
                            if (!is_array($row)) {
                                continue;
                            }
                            $label = is_string($row['label'] ?? null) ? (string) $row['label'] : '';
                            $value = is_string($row['value'] ?? null) ? (string) $row['value'] : '';
                            $text = trim($label) !== '' ? trim($label) . ': ' . $value : $value;
                            $text = trim($text);
                            if ($text === '') {
                                continue;
                            }
                            $featuresView['legacyItems'][] = [
                                'status' => ($row['status'] ?? true) !== false,
                                'text' => $text,
                            ];
                        }
                    }

                    $planCta = is_string($plan->cta_text ?? null) && trim((string) $plan->cta_text) !== '' ? (string) $plan->cta_text : null;
                @endphp

                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-2 {{ $isFeatured ? 'border-primary-500 ring-2 ring-primary-500' : 'border-gray-200 dark:border-gray-700' }} p-8">
                    @if($isFeatured)
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <span class="bg-primary-500 text-white px-4 py-1 rounded-full text-sm font-semibold">{{ $popularBadge }}</span>
                        </div>
                    @endif
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                        <div class="mt-6">
                            <span class="text-5xl font-extrabold text-gray-900 dark:text-white"><span class="text-base font-semibold align-top">{{ $plan->currency }}</span> {{ number_format((float) $plan->price, 2) }}</span>
                            <span class="text-lg font-medium text-gray-500 dark:text-gray-400">/{{ $cycle }}</span>
                        </div>
                    </div>

                    @if(($featuresView['type'] ?? 'legacy') === 'proscons')
                        <div class="mt-8">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ __('Features') }}</div>
                            <ul class="space-y-3">
                                @foreach(($featuresView['pros'] ?? []) as $f)
                                    @if(is_string($f) && trim($f) !== '')
                                        <li class="flex items-start">
                                            <svg class="flex-shrink-0 h-6 w-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            <span class="ml-3 text-base text-gray-700 dark:text-gray-300">{{ $f }}</span>
                                        </li>
                                    @endif
                                @endforeach
                                @foreach(($featuresView['cons'] ?? []) as $f)
                                    @if(is_string($f) && trim($f) !== '')
                                        <li class="flex items-start">
                                            <svg class="flex-shrink-0 h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            <span class="ml-3 text-base text-gray-700 dark:text-gray-300">{{ $f }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <ul class="mt-8 space-y-4">
                            @foreach(($featuresView['legacyItems'] ?? []) as $row)
                                <li class="flex items-start">
                                    @if(($row['status'] ?? true) === false)
                                        <svg class="flex-shrink-0 h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    @else
                                        <svg class="flex-shrink-0 h-6 w-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    @endif
                                    <span class="ml-3 text-base text-gray-700 dark:text-gray-300">{{ $row['text'] ?? '' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-8">
                        @auth('customer')
                            <a href="{{ route('customer.billing.checkout.show', $plan) }}" class="block w-full text-center px-6 py-3 border border-transparent rounded-md text-base font-medium text-white {{ $isFeatured ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600' }}">
                                {{ $planCta ?? $ctaAuth }}
                            </a>
                        @else
                            <a href="{{ route('pricing.checkout', $plan) }}" class="block w-full text-center px-6 py-3 border border-transparent rounded-md text-base font-medium text-white {{ $isFeatured ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600' }}">
                                {{ $planCta ?? $ctaGuest }}
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
            </div>

            @if(!$pricingForceShowAll)
            <div class="mt-20 grid {{ $gridClass }}" x-show="annual">
            @foreach($pricingPlansYearly as $plan)
                @php
                    $isFeatured = ($plan->is_popular ?? false) === true;
                    if (!$isFeatured && $pricingPlansYearly->where('is_popular', true)->count() === 0) {
                        $isFeatured = ((int) $loop->index) === 1;
                    }
                    $cycle = $plan->billing_cycle === 'yearly' ? 'year' : 'month';

                    $quota = $plan->customerGroup?->limit('sending_quota.monthly_quota', 0);
                    $maxSubscribers = $plan->customerGroup?->limit('lists.limits.max_subscribers', 0);
                    $maxCampaigns = $plan->customerGroup?->limit('campaigns.limits.max_campaigns', 0);

                    $quotaLabel = $quota !== null ? number_format((float) $quota) : '—';
                    $subsLabel = $maxSubscribers !== null ? number_format((int) $maxSubscribers) : '—';
                    $campaignsLabel = $maxCampaigns !== null ? number_format((int) $maxCampaigns) : '—';

                    $featuresView = [
                        'type' => 'legacy',
                        'pros' => [],
                        'cons' => [],
                        'legacyItems' => [],
                    ];

                    $planFeatures = $plan->features ?? null;
                    if (is_array($planFeatures) && (array_key_exists('pros', $planFeatures) || array_key_exists('cons', $planFeatures))) {
                        $featuresView['type'] = 'proscons';
                        $featuresView['pros'] = is_array($planFeatures['pros'] ?? null) ? $planFeatures['pros'] : [];
                        $featuresView['cons'] = is_array($planFeatures['cons'] ?? null) ? $planFeatures['cons'] : [];
                    } elseif (is_array($planFeatures) && count($planFeatures) > 0) {
                        $featuresView['type'] = 'proscons';
                        $featuresView['pros'] = $planFeatures;
                        $featuresView['cons'] = [];
                    } else {
                        $features = $plan->customerGroup?->displayAccessAndLimits() ?? [];
                        foreach ($features as $row) {
                            if (!is_array($row)) {
                                continue;
                            }

                            $label = is_string($row['label'] ?? null) ? (string) $row['label'] : '';
                            $value = is_string($row['value'] ?? null) ? (string) $row['value'] : '';
                            $text = trim($label) !== '' ? trim($label) . ': ' . $value : $value;
                            $text = trim($text);

                            if ($text === '') {
                                continue;
                            }

                            $featuresView['legacyItems'][] = [
                                'status' => ($row['status'] ?? true) !== false,
                                'text' => $text,
                            ];
                        }
                    }

                    $planCta = is_string($plan->cta_text ?? null) && trim((string) $plan->cta_text) !== '' ? (string) $plan->cta_text : null;
                @endphp

                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-2 {{ $isFeatured ? 'border-primary-500 ring-2 ring-primary-500' : 'border-gray-200 dark:border-gray-700' }} p-8">
                    @if($isFeatured)
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <span class="bg-primary-500 text-white px-4 py-1 rounded-full text-sm font-semibold">{{ $popularBadge }}</span>
                        </div>
                    @endif

                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>

                        <div class="mt-6">
                            <span class="text-5xl font-extrabold text-gray-900 dark:text-white"><span class="text-base font-semibold align-top">{{ $plan->currency }}</span> {{ number_format((float) $plan->price, 2) }}</span>
                            <span class="text-lg font-medium text-gray-500 dark:text-gray-400">/{{ $cycle }}</span>
                        </div>
                    </div>

                    @if(($featuresView['type'] ?? 'legacy') === 'proscons')
                        <div class="mt-8">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ __('Features') }}</div>
                            <ul class="space-y-3">
                                @foreach(($featuresView['pros'] ?? []) as $f)
                                    @if(is_string($f) && trim($f) !== '')
                                        <li class="flex items-start">
                                            <svg class="flex-shrink-0 h-6 w-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            <span class="ml-3 text-base text-gray-700 dark:text-gray-300">{{ $f }}</span>
                                        </li>
                                    @endif
                                @endforeach

                                @foreach(($featuresView['cons'] ?? []) as $f)
                                    @if(is_string($f) && trim($f) !== '')
                                        <li class="flex items-start">
                                            <svg class="flex-shrink-0 h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            <span class="ml-3 text-base text-gray-700 dark:text-gray-300">{{ $f }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <ul class="mt-8 space-y-4">
                            @foreach(($featuresView['legacyItems'] ?? []) as $row)
                                <li class="flex items-start">
                                    @if(($row['status'] ?? true) === false)
                                        <svg class="flex-shrink-0 h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @else
                                        <svg class="flex-shrink-0 h-6 w-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                    <span class="ml-3 text-base text-gray-700 dark:text-gray-300">
                                        {{ $row['text'] ?? '' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-8">
                        @auth('customer')
                            <a href="{{ route('customer.billing.checkout.show', $plan) }}" class="block w-full text-center px-6 py-3 border border-transparent rounded-md text-base font-medium text-white {{ $isFeatured ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600' }}">
                                {{ $planCta ?? $ctaAuth }}
                            </a>
                        @else
                            <a href="{{ route('pricing.checkout', $plan) }}" class="block w-full text-center px-6 py-3 border border-transparent rounded-md text-base font-medium text-white {{ $isFeatured ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600' }}">
                                {{ $planCta ?? $ctaGuest }}
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
            </div>
            @endif
        </div>

        <!-- FAQ Section -->
        <div class="mt-24">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $faqTitle }}</h2>
            </div>
            <div class="mt-12 max-w-3xl mx-auto space-y-8">
                @foreach($faqRows as $row)
                    @php
                        $q = is_array($row) && is_string($row['q'] ?? null) ? (string) $row['q'] : '';
                        $a = is_array($row) && is_string($row['a'] ?? null) ? (string) $row['a'] : '';
                    @endphp
                    @if(trim($q) !== '' || trim($a) !== '')
                        <div>
                            @if(trim($q) !== '')
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $q }}</h3>
                            @endif
                            @if(trim($a) !== '')
                                <p class="mt-2 text-base text-gray-500 dark:text-gray-400">{{ $a }}</p>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

