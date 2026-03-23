@extends('layouts.public')

@section('title', 'Self-Hosted Email Marketing Platform')
@section('pageId', 'home-5')

@php
    use App\Models\Setting;
    $templateOverrides = isset($templateOverrides) && is_array($templateOverrides) ? $templateOverrides : [];
    $navTheme = 'light';
    
    // Dynamic content from settings
    $heroTitle = is_string($templateOverrides['hero_title'] ?? null)
        ? (string) $templateOverrides['hero_title']
        : Setting::get('hero_title', 'The Future of Email Marketing');
    $heroSubtitle = is_string($templateOverrides['hero_subtitle'] ?? null)
        ? (string) $templateOverrides['hero_subtitle']
        : Setting::get('hero_subtitle', 'Launch powerful email campaigns with our self-hosted platform. Complete control, no limits, maximum deliverability.');
    $ctaText = is_string($templateOverrides['cta_text'] ?? null)
        ? (string) $templateOverrides['cta_text']
        : Setting::get('cta_text', 'Start Free Trial');
    $ctaSecondaryText = is_string($templateOverrides['cta_secondary_text'] ?? null)
        ? (string) $templateOverrides['cta_secondary_text']
        : Setting::get('cta_secondary_text', 'Watch Demo');
    
    // Stats
    $statEmails = is_string($templateOverrides['stat_emails_sent'] ?? null)
        ? (string) $templateOverrides['stat_emails_sent']
        : Setting::get('stat_emails_sent', '50M+');
    $statUsers = is_string($templateOverrides['stat_users'] ?? null)
        ? (string) $templateOverrides['stat_users']
        : Setting::get('stat_users', '10K+');
    $statUptime = is_string($templateOverrides['stat_uptime'] ?? null)
        ? (string) $templateOverrides['stat_uptime']
        : Setting::get('stat_uptime', '99.9%');
    
    // Features
    $features = [
        [
            'icon' => 'mail',
            'title' => 'Advanced Campaigns',
            'description' => 'Create beautiful, responsive email campaigns with our drag-and-drop builder and smart templates.'
        ],
        [
            'icon' => 'users',
            'title' => 'Audience Segmentation',
            'description' => 'Target the right subscribers with powerful segmentation based on behavior and preferences.'
        ],
        [
            'icon' => 'bar-chart-2',
            'title' => 'Real-time Analytics',
            'description' => 'Track opens, clicks, and conversions with detailed analytics and visual reports.'
        ],
        [
            'icon' => 'zap',
            'title' => 'Marketing Automation',
            'description' => 'Set up automated workflows, drip campaigns, and trigger-based emails effortlessly.'
        ],
        [
            'icon' => 'shield',
            'title' => 'Data Privacy',
            'description' => 'Self-hosted solution means your data stays on your servers. Full GDPR compliance.'
        ],
        [
            'icon' => 'code',
            'title' => 'API & Integrations',
            'description' => 'Connect with your favorite tools through our REST API and native integrations.'
        ]
    ];
    
    // Integrations
    $integrationIcons = [
        is_string($templateOverrides['integration_1_icon'] ?? null) ? (string) $templateOverrides['integration_1_icon'] : 'zap',
        is_string($templateOverrides['integration_2_icon'] ?? null) ? (string) $templateOverrides['integration_2_icon'] : 'message-square',
        is_string($templateOverrides['integration_3_icon'] ?? null) ? (string) $templateOverrides['integration_3_icon'] : 'globe',
        is_string($templateOverrides['integration_4_icon'] ?? null) ? (string) $templateOverrides['integration_4_icon'] : 'shopping-bag',
        is_string($templateOverrides['integration_5_icon'] ?? null) ? (string) $templateOverrides['integration_5_icon'] : 'credit-card',
        is_string($templateOverrides['integration_6_icon'] ?? null) ? (string) $templateOverrides['integration_6_icon'] : 'target',
    ];
    $integrations = [
        ['name' => 'Zapier', 'icon' => $integrationIcons[0]],
        ['name' => 'Slack', 'icon' => $integrationIcons[1]],
        ['name' => 'WordPress', 'icon' => $integrationIcons[2]],
        ['name' => 'Shopify', 'icon' => $integrationIcons[3]],
        ['name' => 'Stripe', 'icon' => $integrationIcons[4]],
        ['name' => 'HubSpot', 'icon' => $integrationIcons[5]],
    ];
    
    // Testimonials
    $testimonials = [
        [
            'quote' => 'This platform transformed our email marketing. We saw a 40% increase in open rates within the first month.',
            'author' => 'Sarah Johnson',
            'role' => 'Marketing Director',
            'company' => 'TechStart Inc.'
        ],
        [
            'quote' => 'Finally, a self-hosted solution that doesn\'t compromise on features. The automation capabilities are incredible.',
            'author' => 'Michael Chen',
            'role' => 'CEO',
            'company' => 'GrowthLabs'
        ],
        [
            'quote' => 'The best investment we made for our marketing stack. Full control, great deliverability, amazing support.',
            'author' => 'Emily Rodriguez',
            'role' => 'Head of Growth',
            'company' => 'ScaleUp Co.'
        ]
    ];
    
    // Pricing plans
    $plans = [
        [
            'name' => 'Starter',
            'monthly' => 0,
            'yearly' => 0,
            'description' => 'Ideal for individuals or small teams exploring task management basics.',
            'cta' => 'Try for free',
            'featured' => false,
            'features' => ['Up to 3 users', 'Basic task management', 'Drag-and-drop builder', 'Task deadlines & reminders', 'Mobile access'],
        ],
        [
            'name' => 'Professional',
            'monthly' => 12,
            'yearly' => 10,
            'description' => 'Built for teams that need speed, structure, and real-time collaboration.',
            'cta' => 'Try for free',
            'featured' => true,
            'features' => ['Up to 10 users', 'Advanced task management', 'Drag-and-drop builder', 'Task deadlines & reminders', 'Mobile access', 'Priority support', '1-1 calls'],
        ],
        [
            'name' => 'Enterprise',
            'monthly' => 200,
            'yearly' => 168,
            'description' => 'At the power, customization, and support your organization needs.',
            'cta' => 'Get started',
            'featured' => false,
            'features' => ['Unlimited users', 'Advanced management', 'Drag-and-drop builder', 'Task deadlines & reminders', 'Mobile access'],
        ]
    ];
    
    // FAQs
    $faqs = [
        [
            'question' => 'What makes this different from other email marketing platforms?',
            'answer' => 'Our platform is fully self-hosted, giving you complete control over your data and infrastructure. No per-email fees, no subscriber limits imposed by third parties, and maximum deliverability through your own servers.'
        ],
        [
            'question' => 'How difficult is the installation process?',
            'answer' => 'We provide one-click installers for popular hosting platforms, Docker containers, and detailed documentation. Most users are up and running within 15 minutes.'
        ],
        [
            'question' => 'Can I migrate from my current email service?',
            'answer' => 'Yes! We offer free migration assistance and support imports from all major email marketing platforms including Mailchimp, SendGrid, and ConvertKit.'
        ],
        [
            'question' => 'What kind of support do you offer?',
            'answer' => 'All plans include email support. Professional and Enterprise plans get priority support with faster response times, and Enterprise customers get a dedicated account manager.'
        ]
    ];
@endphp

@section('content')
@php
    $hidePublicNav = true;
@endphp

<style>
    svg [fill="#DC325E"], svg [fill="#dc325e"], svg [fill="#e11d48"], svg [fill="#E11D48"] { fill: var(--brand-color); }
    svg [stroke="#DC325E"], svg [stroke="#dc325e"], svg [stroke="#e11d48"], svg [stroke="#E11D48"] { stroke: var(--brand-color); }
</style>

<section class="relative overflow-hidden bg-[rgba(var(--brand-rgb),0.04)]">
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[rgba(var(--brand-rgb),0.04)] via-[rgba(var(--brand-rgb),0.03)] to-white"></div>
    <div class="pointer-events-none absolute -top-36 left-1/2 h-[520px] w-[980px] -translate-x-1/2 rounded-full bg-gradient-to-r from-[rgba(var(--brand-rgb),0.20)] via-[rgba(var(--brand-rgb),0.14)] to-[rgba(var(--brand-rgb),0.10)] blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 right-0 h-56 bg-gradient-to-t from-[rgba(var(--brand-rgb),0.12)] to-transparent"></div>

    <div class="relative" x-data="{ mobileNavOpen: false }">
        <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-gray-900">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 12.6a8 8 0 1 1-1.7-8.7"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 4v6h-6"/></svg>
                    </span>
                    <span>Logoipsum</span>
                </a>

                <div class="hidden items-center gap-6 md:flex">
                    <a href="#" class="text-sm font-medium text-[var(--brand-color)]">Home Page</a>
                    <a href="#" class="text-sm font-medium text-gray-600 hover:text-gray-900">Product</a>
                    <a href="#features" class="text-sm font-medium text-gray-600 hover:text-gray-900">Features</a>
                    <a href="#pricing" class="text-sm font-medium text-gray-600 hover:text-gray-900">Pricing</a>
                    <a href="#" class="text-sm font-medium text-gray-600 hover:text-gray-900">Resources</a>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" class="hidden items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm md:inline-flex" @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode ? 'true' : 'false')">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-100">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                        </span>
                        Dark
                    </button>
                    <a href="#" class="hidden items-center rounded-lg bg-[var(--brand-color)] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:brightness-90 md:inline-flex">Contact Us</a>

                    <button type="button" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-700 shadow-sm hover:bg-gray-50 md:hidden" @click="mobileNavOpen = true">
                        <span class="sr-only">Open menu</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileNavOpen" x-transition.opacity class="fixed inset-0 z-50 md:hidden" style="display: none;">
            <div class="absolute inset-0 bg-black/30" @click="mobileNavOpen = false"></div>
            <div class="absolute right-0 top-0 h-full w-80 max-w-[90vw] bg-white shadow-xl" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-4">
                    <div class="flex items-center gap-2 font-semibold text-gray-900">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 12.6a8 8 0 1 1-1.7-8.7"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 4v6h-6"/></svg>
                        </span>
                        <span>Logoipsum</span>
                    </div>
                    <button type="button" class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100" @click="mobileNavOpen = false">
                        <span class="sr-only">Close menu</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-4 py-4">
                    <nav class="space-y-2">
                        <a href="#" class="block rounded-lg px-3 py-2 text-sm font-medium text-[var(--brand-color)] hover:bg-[rgba(var(--brand-rgb),0.08)]" @click="mobileNavOpen = false">Home Page</a>
                        <a href="#" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileNavOpen = false">Product</a>
                        <a href="#features" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileNavOpen = false">Features</a>
                        <a href="#pricing" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileNavOpen = false">Pricing</a>
                        <a href="#" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="mobileNavOpen = false">Resources</a>
                    </nav>

                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <a href="#" class="inline-flex items-center justify-center rounded-lg bg-[var(--brand-color)] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:brightness-90" @click="mobileNavOpen = false">Contact Us</a>
                        <button type="button" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50" @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode ? 'true' : 'false')">Toggle Dark</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto w-full max-w-6xl px-4 py-40 sm:px-6 lg:px-8">
        <div class="relative">
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-1 py-1 pr-4 text-xs font-semibold text-gray-700 shadow-sm">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="24" rx="12" fill="#DC325E"/>
                        <path d="M18.3334 12C18.3334 15.4978 15.4979 18.3333 12.0001 18.3333C10.9147 18.3333 9.89301 18.0603 9.00008 17.5791C7.75459 16.908 6.91649 17.5319 6.17736 17.6439C6.06524 17.6609 5.95357 17.6201 5.87339 17.54C5.75169 17.4183 5.72852 17.2301 5.79575 17.0716C6.08585 16.3879 6.35221 15.0921 5.98902 14C5.77995 13.3713 5.66675 12.6989 5.66675 12C5.66675 8.5022 8.50227 5.66667 12.0001 5.66667C15.4979 5.66667 18.3334 8.5022 18.3334 12Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.9999 14.6664C11.9999 14.6664 9.33325 13.0193 9.33325 11.4257C9.33325 10.6382 9.89465 9.99976 10.6666 9.99976C11.0666 9.99976 11.4666 10.137 11.9999 10.686C12.5332 10.137 12.9332 9.99976 13.3332 9.99976C14.1052 9.99976 14.6666 10.6382 14.6666 11.4257C14.6666 13.0193 11.9999 14.6664 11.9999 14.6664Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Trusted by 10,000+ businesses
                </div>

                <h1 class="mt-7 text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl" data-mp-gsap-words="hero-headline">
                    Powerful Email Marketing
                    <span class="block">
                        That <span class="text-[var(--brand-color)]">Grows</span> Your <span class="text-[var(--brand-color)]">Business</span>
                    </span>
                </h1>

                <p class="mt-5 text-base text-gray-600 sm:text-lg">
                    Create, automate, and analyze email campaigns with ease. Reach the right audience at the right time.
                </p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-black">Start Free Trial</a>
                    <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50">Book a Live Demo</a>
                </div>
            </div>

            <img data-mp-gsap-float="hero" data-mp-gsap-float-i="0" src="https://i.ibb.co/0j8j0Fmk/image.png" alt="image" border="0" style="width: 190px; position: absolute; top: 0px; transform: translateX(-100px) rotate(-10deg);">
            <img data-mp-gsap-float="hero" data-mp-gsap-float-i="1" src="https://i.ibb.co/VpSSGMFF/image.png" alt="image" border="0" style="width: 190px; position: absolute; top: 300px; transform: translateX(-80px) rotate(10deg);">
            <img data-mp-gsap-float="hero" data-mp-gsap-float-i="2" src="https://i.ibb.co/fdjzYXS0/image.png" alt="image" border="0" style="width: 220px; position: absolute; top: 100px; transform: translateX(950px) rotate(10deg);">
            {{-- DESIGN IMG 2 --}}
            {{-- DESIGN IMG 3 --}}
            
        </div>

    </div>
</section>

<section class="bg-[#f7f8fc] py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <img src="https://i.ibb.co/DfMbdKfZ/Frame-2147229583.png" alt="Frame 2147229583" border="0">
            </div>

            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-1 py-1 pr-4 text-xs font-semibold text-gray-700 shadow-sm">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="24" rx="12" fill="#DC325E"/>
                        <path d="M18.3334 12C18.3334 15.4978 15.4979 18.3333 12.0001 18.3333C10.9147 18.3333 9.89301 18.0603 9.00008 17.5791C7.75459 16.908 6.91649 17.5319 6.17736 17.6439C6.06524 17.6609 5.95357 17.6201 5.87339 17.54C5.75169 17.4183 5.72852 17.2301 5.79575 17.0716C6.08585 16.3879 6.35221 15.0921 5.98902 14C5.77995 13.3713 5.66675 12.6989 5.66675 12C5.66675 8.5022 8.50227 5.66667 12.0001 5.66667C15.4979 5.66667 18.3334 8.5022 18.3334 12Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.9999 14.6664C11.9999 14.6664 9.33325 13.0193 9.33325 11.4257C9.33325 10.6382 9.89465 9.99976 10.6666 9.99976C11.0666 9.99976 11.4666 10.137 11.9999 10.686C12.5332 10.137 12.9332 9.99976 13.3332 9.99976C14.1052 9.99976 14.6666 10.6382 14.6666 11.4257C14.6666 13.0193 11.9999 14.6664 11.9999 14.6664Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Core features.
                </div>

                <h2 class="mt-4 text-4xl font-extrabold leading-tight tracking-tight text-gray-900" data-mp-gsap-words="core-features-headline">
                    Everything You Need
                    <span class="block">to Succeed</span>
                </h2>

                <p class="mt-4 max-w-xl text-sm leading-6 text-gray-500">
                    Powerful features designed to help you create, send, and optimize your
                    email marketing campaigns.
                </p>

                <div class="mt-8 grid gap-6 sm:grid-cols-2">
                    <div class="flex gap-3">
                        <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.1724 2.90215C8.3708 2.36595 9.1292 2.36595 9.3276 2.90215L10.2169 5.30537C10.3416 5.64254 10.6075 5.90836 10.9446 6.03312L13.3479 6.9224C13.884 7.1208 13.884 7.8792 13.3479 8.0776L10.9446 8.96688C10.6075 9.09164 10.3416 9.35746 10.2169 9.69463L9.3276 12.0978C9.1292 12.634 8.37081 12.634 8.1724 12.0978L7.28313 9.69463C7.15836 9.35746 6.89254 9.09164 6.55538 8.96688L4.15215 8.0776C3.61595 7.8792 3.61595 7.12081 4.15215 6.9224L6.55538 6.03312C6.89254 5.90836 7.15836 5.64254 7.28313 5.30537L8.1724 2.90215Z" stroke="#DC325E" stroke-width="1.875"/>
                                <path d="M18.0049 12.0517L19.8586 13.9055M7.5 27.5H9.37199C10.8875 27.5 11.6452 27.5 12.3266 27.2178C13.0079 26.9355 13.5437 26.3998 14.6154 25.3281L24.8021 15.1414C25.476 14.4675 25.8129 14.1306 25.993 13.7671C26.3356 13.0756 26.3356 12.2637 25.993 11.5722C25.8129 11.2087 25.476 10.8718 24.8021 10.1979C24.1282 9.52405 23.7914 9.18713 23.4279 9.00702C22.7364 8.66433 21.9244 8.66433 21.2329 9.00702C20.8694 9.18713 20.5325 9.52405 19.8586 10.1979L9.67186 20.3846C8.60025 21.4563 8.06445 21.9921 7.78223 22.6735C7.5 23.3548 7.5 24.1125 7.5 25.628V27.5Z" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Drag &amp; Drop Editor</div>
                            <div class="mt-1 text-xs text-gray-500">Design beautiful emails without coding.</div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7.5 6.875C7.5 5.70672 7.5 5.1226 7.7512 4.6875C7.91576 4.40246 8.15246 4.16576 8.4375 4.0012C8.8726 3.75 9.45672 3.75 10.625 3.75H14.375C15.5432 3.75 16.1274 3.75 16.5625 4.0012C16.8475 4.16576 17.0842 4.40246 17.2487 4.6875C17.5 5.1226 17.5 5.70672 17.5 6.875C17.5 8.04328 17.5 8.6274 17.2487 9.0625C17.0842 9.34754 16.8475 9.58424 16.5625 9.7488C16.1274 10 15.5432 10 14.375 10H10.625C9.45672 10 8.8726 10 8.4375 9.7488C8.15246 9.58424 7.91576 9.34754 7.7512 9.0625C7.5 8.6274 7.5 8.04328 7.5 6.875Z" stroke="#DC325E" stroke-width="1.875"/>
                                <path d="M7.5 19.375C7.5 18.2068 7.5 17.6226 7.7512 17.1875C7.91576 16.9025 8.15246 16.6658 8.4375 16.5013C8.8726 16.25 9.45672 16.25 10.625 16.25H14.375C15.5432 16.25 16.1274 16.25 16.5625 16.5013C16.8475 16.6658 17.0842 16.9025 17.2487 17.1875C17.5 17.6226 17.5 18.2068 17.5 19.375C17.5 20.5432 17.5 21.1274 17.2487 21.5625C17.0842 21.8475 16.8475 22.0842 16.5625 22.2487C16.1274 22.5 15.5432 22.5 14.375 22.5H10.625C9.45672 22.5 8.8726 22.5 8.4375 22.2487C8.15246 22.0842 7.91576 21.8475 7.7512 21.5625C7.5 21.1274 7.5 20.5432 7.5 19.375Z" stroke="#DC325E" stroke-width="1.875"/>
                                <path d="M26.0497 21.426C27.0166 20.4591 27.5 19.9757 27.5 19.375C27.5 18.7743 27.0166 18.2909 26.0497 17.324L25.801 17.0753C24.8341 16.1084 24.3507 15.625 23.75 15.625C23.1493 15.625 22.6659 16.1084 21.699 17.0753L21.4503 17.324C20.4834 18.2909 20 18.7743 20 19.375C20 19.9757 20.4834 20.4591 21.4503 21.426L21.699 21.6747C22.6659 22.6416 23.1493 23.125 23.75 23.125C24.3507 23.125 24.8341 22.6416 25.801 21.6747L26.0497 21.426Z" stroke="#DC325E" stroke-width="1.875"/>
                                <path d="M7.50735 6.875H2.50961M2.50961 6.875V2.5M2.50961 6.875V15C2.50961 16.3796 2.32049 18.0849 3.62019 18.9536C4.25046 19.375 5.12785 19.375 6.88264 19.375M17.5028 19.375H20.0017M23.75 15.625V11.875C23.75 9.51798 23.75 8.33946 23.0181 7.60724C22.2862 6.875 21.1082 6.875 18.7522 6.875H17.5028" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M27.5 27.5C26.3351 27.5 25.7528 27.5 25.2934 27.3369C24.6808 27.1194 24.1941 26.7022 23.9404 26.1771C23.75 25.7834 23.75 25.2841 23.75 24.2858V23.75" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Automation Workflows</div>
                            <div class="mt-1 text-xs text-gray-500">Send the right message at the perfect time.</div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20.625 25V22.463C20.625 20.9101 19.9259 19.3874 18.5129 18.7433C16.7894 17.9576 14.7224 17.5 12.5 17.5C10.2777 17.5 8.21063 17.9576 6.4871 18.7433C5.07409 19.3874 4.375 20.9101 4.375 22.463V25" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M25.625 25.0012V22.4641C25.625 20.9112 24.9259 19.3886 23.5129 18.7445C23.1871 18.596 22.849 18.4591 22.5 18.335" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12.5 13.75C14.9162 13.75 16.875 11.7912 16.875 9.375C16.875 6.95875 14.9162 5 12.5 5C10.0838 5 8.125 6.95875 8.125 9.375C8.125 11.7912 10.0838 13.75 12.5 13.75Z" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M18.75 5.18066C20.5571 5.71851 21.875 7.3926 21.875 9.37449C21.875 11.3564 20.5571 13.0305 18.75 13.5684" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Audience Segmentation</div>
                            <div class="mt-1 text-xs text-gray-500">Audience Segmentation</div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.8511 16.44L10 8.75L16.7745 14.0995C17.7376 14.86 17.7426 16.309 16.7846 17.0759C15.8268 17.8429 14.3986 17.5333 13.8511 16.44Z" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6.25 6.02494C3.9358 8.29872 2.5 11.4673 2.5 14.9719C2.5 21.8911 8.09644 27.5001 15 27.5001C21.9035 27.5001 27.5 21.8911 27.5 14.9719C27.5 8.90519 23.1976 3.84564 17.484 2.69104C16.439 2.47987 15.9165 2.37428 15.4583 2.75022C15 3.12616 15 3.73387 15 4.94931V6.20213" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Real-Time Analytics</div>
                            <div class="mt-1 text-xs text-gray-500">Track opens, clicks, and conversions.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 grid max-w-xl grid-cols-3 gap-6">
                    <div>
                        <div class="text-2xl font-extrabold text-gray-900">5.6k</div>
                        <div class="mt-1 text-[11px] text-gray-500">Completed Project</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-gray-900">268%</div>
                        <div class="mt-1 text-[11px] text-gray-500">Customer Engagement</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-gray-900">96%</div>
                        <div class="mt-1 text-[11px] text-gray-500">Satisfied Our Client</div>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="#" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50">
                        Learn more about it
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-gray-900 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-white py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-1 py-1 pr-4 text-xs font-semibold text-gray-700 shadow-sm">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="24" rx="12" fill="#DC325E"/>
                        <path d="M18.3334 12C18.3334 15.4978 15.4979 18.3333 12.0001 18.3333C10.9147 18.3333 9.89301 18.0603 9.00008 17.5791C7.75459 16.908 6.91649 17.5319 6.17736 17.6439C6.06524 17.6609 5.95357 17.6201 5.87339 17.54C5.75169 17.4183 5.72852 17.2301 5.79575 17.0716C6.08585 16.3879 6.35221 15.0921 5.98902 14C5.77995 13.3713 5.66675 12.6989 5.66675 12C5.66675 8.5022 8.50227 5.66667 12.0001 5.66667C15.4979 5.66667 18.3334 8.5022 18.3334 12Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.9999 14.6664C11.9999 14.6664 9.33325 13.0193 9.33325 11.4257C9.33325 10.6382 9.89465 9.99976 10.6666 9.99976C11.0666 9.99976 11.4666 10.137 11.9999 10.686C12.5332 10.137 12.9332 9.99976 13.3332 9.99976C14.1052 9.99976 14.6666 10.6382 14.6666 11.4257C14.6666 13.0193 11.9999 14.6664 11.9999 14.6664Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Email templates
                </div>
                <h2 class="mt-4 max-w-xl text-4xl font-extrabold leading-tight tracking-tight text-gray-900">
                    Professionally Designed
                    <span class="block">Email Templates</span>
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="rounded-lg bg-[var(--brand-color)] px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:brightness-90">All Template</button>
                <button type="button" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700">Newsletter</button>
                <button type="button" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700">Marketing</button>
                <button type="button" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700">Transactional</button>
            </div>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="relative aspect-[4/3] bg-gradient-to-br from-gray-900 via-gray-700 to-gray-500">
                    <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>
                </div>
                <div class="flex items-center justify-between gap-3 bg-[var(--brand-color)] px-4 py-3">
                    <div>
                        <div class="inline-flex items-center gap-2 text-[11px] font-semibold text-white/90">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded bg-white/20">
                                <div class="h-2.5 w-2.5 rounded bg-white/60"></div>
                            </span>
                            Newsletter
                        </div>
                        <div class="mt-1 text-xs font-semibold text-white">Weekly Newsletter</div>
                    </div>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/15">
                        <div class="h-4 w-4 rounded bg-white/60"></div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="relative aspect-[4/3] bg-gradient-to-br from-gray-800 via-gray-600 to-gray-400">
                    <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>
                </div>
                <div class="bg-gray-900/55 px-4 py-3">
                    <div class="inline-flex items-center gap-2 text-[11px] font-semibold text-white/90">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded bg-white/15">
                            <div class="h-2.5 w-2.5 rounded bg-white/60"></div>
                        </span>
                        Newsletter
                    </div>
                    <div class="mt-1 text-xs font-semibold text-white">Weekly Newsletter</div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="relative aspect-[4/3] bg-gradient-to-br from-gray-800 via-gray-600 to-gray-400">
                    <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>
                </div>
                <div class="bg-gray-900/55 px-4 py-3">
                    <div class="inline-flex items-center gap-2 text-[11px] font-semibold text-white/90">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded bg-white/15">
                            <div class="h-2.5 w-2.5 rounded bg-white/60"></div>
                        </span>
                        Newsletter
                    </div>
                    <div class="mt-1 text-xs font-semibold text-white">Weekly Newsletter</div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="relative aspect-[4/3] bg-gradient-to-br from-gray-800 via-gray-600 to-gray-400">
                    <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>
                </div>
                <div class="bg-gray-900/55 px-4 py-3">
                    <div class="inline-flex items-center gap-2 text-[11px] font-semibold text-white/90">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded bg-white/15">
                            <div class="h-2.5 w-2.5 rounded bg-white/60"></div>
                        </span>
                        Newsletter
                    </div>
                    <div class="mt-1 text-xs font-semibold text-white">Weekly Newsletter</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="relative overflow-hidden bg-gradient-to-b from-[rgba(var(--brand-rgb),0.04)] via-[rgba(var(--brand-rgb),0.03)] to-white py-20">
    <div class="pointer-events-none absolute inset-0 opacity-40" style="background-image: radial-gradient(rgba(255,255,255,0.9) 1px, transparent 1px); background-size: 36px 36px;"></div>
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="relative">
            <div class="mx-auto max-w-2xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-1 py-1 pr-4 text-xs font-semibold text-gray-700 shadow-sm">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="24" rx="12" fill="#DC325E"/>
                        <path d="M18.3334 12C18.3334 15.4978 15.4979 18.3333 12.0001 18.3333C10.9147 18.3333 9.89301 18.0603 9.00008 17.5791C7.75459 16.908 6.91649 17.5319 6.17736 17.6439C6.06524 17.6609 5.95357 17.6201 5.87339 17.54C5.75169 17.4183 5.72852 17.2301 5.79575 17.0716C6.08585 16.3879 6.35221 15.0921 5.98902 14C5.77995 13.3713 5.66675 12.6989 5.66675 12C5.66675 8.5022 8.50227 5.66667 12.0001 5.66667C15.4979 5.66667 18.3334 8.5022 18.3334 12Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.9999 14.6664C11.9999 14.6664 9.33325 13.0193 9.33325 11.4257C9.33325 10.6382 9.89465 9.99976 10.6666 9.99976C11.0666 9.99976 11.4666 10.137 11.9999 10.686C12.5332 10.137 12.9332 9.99976 13.3332 9.99976C14.1052 9.99976 14.6666 10.6382 14.6666 11.4257C14.6666 13.0193 11.9999 14.6664 11.9999 14.6664Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    How it works.
                </div>

                <h2 class="mp-gsap-words mt-4 text-4xl font-extrabold leading-tight tracking-tight text-gray-900">
                    Launch in minutes—just 3 steps.
                </h2>
                <p class="mt-3 text-sm leading-6 text-gray-500">
                    Get started in minutes with our simple three-step process.
                </p>
            </div>

            <div class="relative mx-auto mt-12 max-w-5xl">
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="relative rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 11.25C20 6.41751 16.0825 2.5 11.25 2.5C6.41751 2.5 2.5 6.41751 2.5 11.25C2.5 16.0825 6.41751 20 11.25 20" stroke="#DC325E" stroke-width="1.875"/>
                            <path d="M20 11.25H18.75C15.2145 11.25 13.4468 11.25 12.3484 12.3484C11.25 13.4468 11.25 15.2145 11.25 18.75V20C11.25 23.5355 11.25 25.3032 12.3484 26.4016C13.4468 27.5 15.2145 27.5 18.75 27.5H20C23.5355 27.5 25.3032 27.5 26.4016 26.4016C27.5 25.3032 27.5 23.5355 27.5 20V18.75C27.5 15.2145 27.5 13.4468 26.4016 12.3484C25.3032 11.25 23.5355 11.25 20 11.25Z" stroke="#DC325E" stroke-width="1.875"/>
                            </svg>
                        </div>
                        <div class="mt-4 text-sm font-semibold text-gray-900">Create Email</div>
                        <div class="mt-2 text-xs leading-5 text-gray-500">Choose a template or start from scratch. Our drag-and-drop editor makes it easy.</div>
                        <div class="mt-6 inline-flex items-center gap-2 rounded-full px-1 py-1 pr-3 text-[11px] font-semibold text-gray-700 ring-1 ring-gray-200">
                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="28" height="28" rx="14" fill="#F7F7F7"/>
                                <path d="M18.6667 14C18.6667 15.1046 17.7713 16 16.6667 16C15.5621 16 14.6667 15.1046 14.6667 14C14.6667 12.8954 15.5621 12 16.6667 12C17.7713 12 18.6667 12.8954 18.6667 14Z" stroke="#4285F4"/>
                                <path d="M16.6666 10H11.3333C9.12411 10 7.33325 11.7909 7.33325 14C7.33325 16.2091 9.12411 18 11.3333 18H16.6666C18.8757 18 20.6666 16.2091 20.6666 14C20.6666 11.7909 18.8757 10 16.6666 10Z" stroke="#4285F4"/>
                                </svg>

                            Step 01
                        </div>
                    </div>

                    <div class="relative rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M26.3096 3.81616C23.5871 0.884202 3.1081 8.0665 3.12501 10.6887C3.14419 13.6624 11.1226 14.5771 13.334 15.1976C14.6639 15.5706 15.02 15.9531 15.3266 17.3476C16.7154 23.6631 17.4126 26.8044 19.0017 26.8745C21.5347 26.9865 28.9666 6.6775 26.3096 3.81616Z" stroke="#DC325E" stroke-width="1.875"/>
                            <path d="M14.375 15.625L18.75 11.25" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                        </div>
                        <div class="mt-4 text-sm font-semibold text-gray-900">Send Message</div>
                        <div class="mt-2 text-xs leading-5 text-gray-500">Schedule or automate your campaign. Reach subscribers at the perfect time.</div>
                        <div class="mt-6 inline-flex items-center gap-2 rounded-full px-1 py-1 pr-3 text-[11px] font-semibold text-gray-700 ring-1 ring-gray-200">
                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="28" height="28" rx="14" fill="#F7F7F7"/>
                                <path d="M13.9999 20.6666C17.6818 20.6666 20.6666 17.6818 20.6666 13.9999C20.6666 10.318 17.6818 7.33325 13.9999 7.33325C10.318 7.33325 7.33325 10.318 7.33325 13.9999C7.33325 17.6818 10.318 20.6666 13.9999 20.6666Z" stroke="#4285F4" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 10C14.7021 10 15.3919 10.1848 16 10.5359C16.6081 10.887 17.113 11.3919 17.4641 12C17.8152 12.6081 18 13.2979 18 14C18 14.7021 17.8152 15.3919 17.4641 16C17.113 16.6081 16.6081 17.113 16 17.4641C15.3919 17.8152 14.7021 18 14 18C13.2979 18 12.6081 17.8152 12 17.4641C11.3919 17.113 10.887 16.6081 10.5359 16L14 14V10Z" stroke="#4285F4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Step 02
                        </div>
                    </div>

                    <div class="relative rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M26.25 26.25H12.5C8.37521 26.25 6.31281 26.25 5.03141 24.9686C3.75 23.6871 3.75 21.6247 3.75 17.5V3.75" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round"/>
                            <path d="M22.131 11.6667L18.5389 17.4806C18.0154 18.3279 17.4211 19.6075 16.3436 19.418C15.0764 19.195 14.4677 17.3061 13.3782 16.6806C12.4911 16.1714 11.8496 16.7851 11.3309 17.5M26.25 5L23.9331 8.75M6.25 25L9.4079 20.3334" stroke="#DC325E" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                        </div>
                        <div class="mt-4 text-sm font-semibold text-gray-900">Analyze &amp; Optimize</div>
                        <div class="mt-2 text-xs leading-5 text-gray-500">Measure performance with detailed reports. Optimize for better results.</div>
                        <div class="mt-6 inline-flex items-center gap-2 rounded-full px-1 py-1 pr-3 text-[11px] font-semibold text-gray-700 ring-1 ring-gray-200">
                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="28" height="28" rx="14" fill="#F7F7F7"/>
                                <path d="M15.5078 8.3995C14.797 7.68867 14.4416 7.33325 13.9999 7.33325C13.5583 7.33325 13.2029 7.68867 12.492 8.3995C12.0655 8.82605 11.6428 9.02409 11.0346 9.02409C10.5037 9.02409 9.74821 8.92111 9.33325 9.33955C8.92158 9.75468 9.02411 10.507 9.02411 11.0346C9.02411 11.6428 8.82605 12.0655 8.39949 12.492C7.68867 13.2029 7.33326 13.5583 7.33325 13.9999C7.33327 14.4415 7.68868 14.797 8.39951 15.5078C8.87736 15.9857 9.02411 16.2942 9.02411 16.9652C9.02411 17.4962 8.92113 18.2517 9.33958 18.6666C9.75471 19.0783 10.507 18.9757 11.0346 18.9757C11.6822 18.9757 11.9941 19.1024 12.4563 19.5646C12.8499 19.9582 13.3775 20.6666 13.9999 20.6666C14.6224 20.6666 15.15 19.9582 15.5435 19.5646C16.0057 19.1024 16.3176 18.9757 16.9652 18.9757C17.4928 18.9757 18.2451 19.0783 18.6603 18.6666M18.6603 18.6666C19.0787 18.2517 18.9757 17.4962 18.9757 16.9652C18.9757 16.2942 19.1225 15.9857 19.6003 15.5078C20.3112 14.797 20.6666 14.4415 20.6666 13.9999C20.6666 13.5583 20.3112 13.2029 19.6003 12.492M18.6603 18.6666H18.6666" stroke="#4285F4" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M11.6667 12.3333L14.0001 14.6667L20.0002 8" stroke="#4285F4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Step 03
                        </div>
                    </div>
                </div>

                <div class="pointer-events-none absolute left-1/3 top-1/2 hidden -translate-y-1/2 md:block">
                    <svg width="32" height="18" viewBox="0 0 32 18" fill="none" aria-hidden="true">
                        <path d="M2 9h24" stroke="rgba(var(--brand-rgb),0.45)" stroke-width="2" stroke-linecap="round" />
                        <path d="M22 3l6 6-6 6" stroke="rgba(var(--brand-rgb),0.65)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="pointer-events-none absolute left-2/3 top-1/2 hidden -translate-y-1/2 md:block">
                    <svg width="32" height="18" viewBox="0 0 32 18" fill="none" aria-hidden="true">
                        <path d="M2 9h24" stroke="rgba(var(--brand-rgb),0.45)" stroke-width="2" stroke-linecap="round" />
                        <path d="M22 3l6 6-6 6" stroke="rgba(var(--brand-rgb),0.65)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-[#f7f8fc] py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-1 py-1 pr-4 text-xs font-semibold text-gray-700 shadow-sm">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="24" rx="12" fill="#DC325E"/>
                        <path d="M18.3334 12C18.3334 15.4978 15.4979 18.3333 12.0001 18.3333C10.9147 18.3333 9.89301 18.0603 9.00008 17.5791C7.75459 16.908 6.91649 17.5319 6.17736 17.6439C6.06524 17.6609 5.95357 17.6201 5.87339 17.54C5.75169 17.4183 5.72852 17.2301 5.79575 17.0716C6.08585 16.3879 6.35221 15.0921 5.98902 14C5.77995 13.3713 5.66675 12.6989 5.66675 12C5.66675 8.5022 8.50227 5.66667 12.0001 5.66667C15.4979 5.66667 18.3334 8.5022 18.3334 12Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.9999 14.6664C11.9999 14.6664 9.33325 13.0193 9.33325 11.4257C9.33325 10.6382 9.89465 9.99976 10.6666 9.99976C11.0666 9.99976 11.4666 10.137 11.9999 10.686C12.5332 10.137 12.9332 9.99976 13.3332 9.99976C14.1052 9.99976 14.6666 10.6382 14.6666 11.4257C14.6666 13.0193 11.9999 14.6664 11.9999 14.6664Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Analytics & Performance.
                </div>


                <h2 class="mp-gsap-words mt-4 text-4xl font-extrabold leading-tight tracking-tight text-gray-900">
                    Track Performance
                    <span class="block">with Smart Analytics.</span>
                </h2>

                <p class="mt-4 max-w-xl text-sm leading-6 text-gray-500">
                    Get actionable insights to optimize your campaigns. Our analytics dashboard shows you exactly what's working.
                </p>

                <div class="mt-6 space-y-3">
                    <div class="flex items-center gap-2 text-md">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33325 15V5" stroke="#DC325E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.83906 7.33631C10.016 6.8879 10.6506 6.8879 10.8276 7.33631L10.8581 7.41406C11.2901 8.5094 12.1572 9.37648 13.2526 9.80848L13.3303 9.83906C13.7787 10.016 13.7787 10.6506 13.3303 10.8276L13.2526 10.8581C12.1572 11.2901 11.2901 12.1572 10.8581 13.2526L10.8276 13.3303C10.6506 13.7787 10.016 13.7787 9.83906 13.3303L9.80848 13.2526C9.37648 12.1572 8.5094 11.2901 7.41406 10.8581L7.33631 10.8276C6.8879 10.6506 6.8879 10.016 7.33631 9.83906L7.41406 9.80848C8.5094 9.37648 9.37648 8.5094 9.80848 7.41406L9.83906 7.33631Z" stroke="#DC325E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>

                        <span class="text-sm font-medium text-gray-700">Open and click rates.</span>
                    </div>
                    <div class="flex items-center gap-2 text-md">
                         <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33325 15V5" stroke="#DC325E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.83906 7.33631C10.016 6.8879 10.6506 6.8879 10.8276 7.33631L10.8581 7.41406C11.2901 8.5094 12.1572 9.37648 13.2526 9.80848L13.3303 9.83906C13.7787 10.016 13.7787 10.6506 13.3303 10.8276L13.2526 10.8581C12.1572 11.2901 11.2901 12.1572 10.8581 13.2526L10.8276 13.3303C10.6506 13.7787 10.016 13.7787 9.83906 13.3303L9.80848 13.2526C9.37648 12.1572 8.5094 11.2901 7.41406 10.8581L7.33631 10.8276C6.8879 10.6506 6.8879 10.016 7.33631 9.83906L7.41406 9.80848C8.5094 9.37648 9.37648 8.5094 9.80848 7.41406L9.83906 7.33631Z" stroke="#DC325E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Campaign comparison.</span>
                    </div>
                    <div class="flex items-center gap-2 text-md">
                         <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33325 15V5" stroke="#DC325E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.83906 7.33631C10.016 6.8879 10.6506 6.8879 10.8276 7.33631L10.8581 7.41406C11.2901 8.5094 12.1572 9.37648 13.2526 9.80848L13.3303 9.83906C13.7787 10.016 13.7787 10.6506 13.3303 10.8276L13.2526 10.8581C12.1572 11.2901 11.2901 12.1572 10.8581 13.2526L10.8276 13.3303C10.6506 13.7787 10.016 13.7787 9.83906 13.3303L9.80848 13.2526C9.37648 12.1572 8.5094 11.2901 7.41406 10.8581L7.33631 10.8276C6.8879 10.6506 6.8879 10.016 7.33631 9.83906L7.41406 9.80848C8.5094 9.37648 9.37648 8.5094 9.80848 7.41406L9.83906 7.33631Z" stroke="#DC325E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Audience growth insights.</span>
                    </div>
                    <div class="flex items-center gap-2 text-md">
                         <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.33325 15V5" stroke="#DC325E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.83906 7.33631C10.016 6.8879 10.6506 6.8879 10.8276 7.33631L10.8581 7.41406C11.2901 8.5094 12.1572 9.37648 13.2526 9.80848L13.3303 9.83906C13.7787 10.016 13.7787 10.6506 13.3303 10.8276L13.2526 10.8581C12.1572 11.2901 11.2901 12.1572 10.8581 13.2526L10.8276 13.3303C10.6506 13.7787 10.016 13.7787 9.83906 13.3303L9.80848 13.2526C9.37648 12.1572 8.5094 11.2901 7.41406 10.8581L7.33631 10.8276C6.8879 10.6506 6.8879 10.016 7.33631 9.83906L7.41406 9.80848C8.5094 9.37648 9.37648 8.5094 9.80848 7.41406L9.83906 7.33631Z" stroke="#DC325E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">A/B testing results.</span>
                    </div>
                </div>

                <div class="mt-10 grid max-w-md grid-cols-3 gap-6">
                    <div>
                        <div class="text-2xl font-extrabold text-gray-900">4.5%</div>
                        <div class="mt-1 text-[11px] text-gray-500">Open Rate</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-gray-900">3.2x</div>
                        <div class="mt-1 text-[11px] text-gray-500">ROI</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-gray-900">120k</div>
                        <div class="mt-1 text-[11px] text-gray-500">Emails Sent</div>
                    </div>
                </div>
            </div>

            <div>
                <img src="https://i.ibb.co/gZccPqYC/image.png" alt="image" border="0">
                <img src="https://i.ibb.co/NnLLYvQ6/image.png" alt="image" border="0" style="margin-left: -200px; position: absolute; width: 400px; margin-top: -278px;">
            </div>
        </div>
    </div>
</section>

<section class="bg-[#f7f8fc] py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-1 py-1 pr-4 text-xs font-semibold text-gray-700 shadow-sm">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="24" height="24" rx="12" fill="#DC325E"/>
                    <path d="M18.3334 12C18.3334 15.4978 15.4979 18.3333 12.0001 18.3333C10.9147 18.3333 9.89301 18.0603 9.00008 17.5791C7.75459 16.908 6.91649 17.5319 6.17736 17.6439C6.06524 17.6609 5.95357 17.6201 5.87339 17.54C5.75169 17.4183 5.72852 17.2301 5.79575 17.0716C6.08585 16.3879 6.35221 15.0921 5.98902 14C5.77995 13.3713 5.66675 12.6989 5.66675 12C5.66675 8.5022 8.50227 5.66667 12.0001 5.66667C15.4979 5.66667 18.3334 8.5022 18.3334 12Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M11.9999 14.6664C11.9999 14.6664 9.33325 13.0193 9.33325 11.4257C9.33325 10.6382 9.89465 9.99976 10.6666 9.99976C11.0666 9.99976 11.4666 10.137 11.9999 10.686C12.5332 10.137 12.9332 9.99976 13.3332 9.99976C14.1052 9.99976 14.6666 10.6382 14.6666 11.4257C14.6666 13.0193 11.9999 14.6664 11.9999 14.6664Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Use cases.
            </div>

            <h2 class="mp-gsap-words mt-4 text-4xl font-extrabold leading-tight tracking-tight text-gray-900">Automation Use Cases.</h2>
            <p class="mt-3 text-sm leading-6 text-gray-500">Set up powerful automations that work 24/7 to engage your audience.</p>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-gradient-to-b from-[rgba(var(--brand-rgb),0.04)] to-[rgba(var(--brand-rgb),0.03)] p-6 shadow-sm">
                <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[var(--brand-color)] text-white shadow-sm">
<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6.25044 8.75L4.72693 9.76566C3.63479 10.4938 3.08871 10.8578 2.79278 11.413C2.49684 11.9682 2.49904 12.6208 2.50341 13.9258C2.5087 15.4969 2.5233 17.0978 2.56374 18.7176C2.65966 22.5609 2.70764 24.4825 4.12064 25.8955C5.53364 27.3086 7.48121 27.3572 11.3764 27.4546C13.7996 27.5151 16.2014 27.5151 18.6245 27.4546C22.5198 27.3572 24.4673 27.3086 25.8803 25.8955C27.2933 24.4825 27.3413 22.5609 27.4371 18.7176C27.4776 17.0978 27.4921 15.4969 27.4975 13.9258C27.5019 12.6208 27.504 11.9682 27.2081 11.413C26.9121 10.8578 26.3661 10.4938 25.2739 9.76566L23.7504 8.75" stroke="white" stroke-width="1.875" stroke-linejoin="round"/>
<path d="M2.5 12.5L11.1413 17.6848C13.0213 18.8128 13.9613 19.3767 15 19.3767C16.0387 19.3767 16.9787 18.8128 18.8587 17.6848L27.5 12.5" stroke="white" stroke-width="1.875" stroke-linejoin="round"/>
<path d="M6.25 15V7.5C6.25 5.14298 6.25 3.96446 6.98224 3.23224C7.71448 2.5 8.89299 2.5 11.25 2.5H18.75C21.107 2.5 22.2855 2.5 23.0178 3.23224C23.75 3.96446 23.75 5.14298 23.75 7.5V15" stroke="white" stroke-width="1.875"/>
<path d="M12.5 12.5H17.5M12.5 7.5H17.5" stroke="white" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

                </div>
                <div class="mt-5 text-sm font-semibold text-gray-900">Welcome Emails.</div>
                <div class="mt-2 text-xs leading-5 text-gray-500">Automatically greet new subscribers with a personalized welcome series.</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-gradient-to-b from-[rgba(var(--brand-rgb),0.04)] to-[rgba(var(--brand-rgb),0.03)] p-6 shadow-sm">
                <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[var(--brand-color)] text-white shadow-sm">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M10 20L20.9001 19.0916C24.3108 18.8075 25.0764 18.0625 25.4544 14.6611L26.25 7.5" stroke="white" stroke-width="1.875" stroke-linecap="round"/>
<path d="M7.5 7.5H27.5" stroke="white" stroke-width="1.875" stroke-linecap="round"/>
<path d="M7.5 27.5C8.88071 27.5 10 26.3807 10 25C10 23.6193 8.88071 22.5 7.5 22.5C6.11929 22.5 5 23.6193 5 25C5 26.3807 6.11929 27.5 7.5 27.5Z" stroke="white" stroke-width="1.875"/>
<path d="M21.25 27.5C22.6307 27.5 23.75 26.3807 23.75 25C23.75 23.6193 22.6307 22.5 21.25 22.5C19.8693 22.5 18.75 23.6193 18.75 25C18.75 26.3807 19.8693 27.5 21.25 27.5Z" stroke="white" stroke-width="1.875"/>
<path d="M10 25H18.75" stroke="white" stroke-width="1.875" stroke-linecap="round"/>
<path d="M2.5 2.5H3.7075C4.88835 2.5 5.91768 3.28074 6.20408 4.39366L9.92315 18.8456C10.1111 19.576 9.95025 20.3496 9.4853 20.952L8.29016 22.5" stroke="white" stroke-width="1.875" stroke-linecap="round"/>
</svg>

                </div>
                <div class="mt-5 text-sm font-semibold text-gray-900">Abandoned Cart.</div>
                <div class="mt-2 text-xs leading-5 text-gray-500">Recover lost sales by reminding customers about items left in their cart.</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-gradient-to-b from-[rgba(var(--brand-rgb),0.04)] to-[rgba(var(--brand-rgb),0.03)] p-6 shadow-sm">
                <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[var(--brand-color)] text-white shadow-sm">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M15 14.375V15.625M16.25 15C16.25 15.6904 15.6904 16.25 15 16.25C14.3096 16.25 13.75 15.6904 13.75 15C13.75 14.3096 14.3096 13.75 15 13.75C15.6904 13.75 16.25 14.3096 16.25 15Z" stroke="white" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M26.1814 16.25C26.2268 15.8396 26.25 15.4225 26.25 15C26.25 8.7868 21.2133 3.75 15 3.75C11.4659 3.75 8.31245 5.37959 6.25 7.92834M3.81866 13.75C3.77329 14.1604 3.75 14.5775 3.75 15C3.75 21.2133 8.7868 26.25 15 26.25C18.5341 26.25 21.6875 24.6204 23.75 22.0716" stroke="white" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M10 8.75H8.75C6.98224 8.75 6.09835 8.75 5.54918 8.20082C5 7.65165 5 6.76776 5 5V3.75" stroke="white" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M20 21.25H21.25C23.0177 21.25 23.9016 21.25 24.4509 21.7991C25 22.3484 25 23.2323 25 25V26.25" stroke="white" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
</svg>

                </div>
                <div class="mt-5 text-sm font-semibold text-gray-900">Re-engagement.</div>
                <div class="mt-2 text-xs leading-5 text-gray-500">Win back inactive subscribers with targeted re-engagement campaigns.</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-gradient-to-b from-[#fff7f8] to-[#fff0f3] p-6 shadow-sm">
                <div class="flex p-2 h-10 w-10 items-center justify-center rounded-xl bg-[#e11d48] text-white shadow-sm">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M27.5 6.875C27.5 9.29125 25.5412 11.25 23.125 11.25C20.7088 11.25 18.75 9.29125 18.75 6.875C18.75 4.45875 20.7088 2.5 23.125 2.5C25.5412 2.5 27.5 4.45875 27.5 6.875Z" stroke="white" stroke-width="1.875"/>
                        <path d="M27.4382 13.75C27.4791 14.1611 27.5 14.5781 27.5 15C27.5 21.9035 21.9035 27.5 15 27.5C8.09644 27.5 2.5 21.9035 2.5 15C2.5 8.09644 8.09644 2.5 15 2.5C15.4219 2.5 15.8389 2.5209 16.25 2.56173" stroke="white" stroke-width="1.875" stroke-linecap="round"/>
                        <path d="M10 12.5H15" stroke="white" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 18.75H20" stroke="white" stroke-width="1.875" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="mt-5 text-sm font-semibold text-gray-900">Product Updates.</div>
                <div class="mt-2 text-xs leading-5 text-gray-500">Keep customers informed about new features and product improvements.</div>
            </div>
        </div>
    </div>
</section>

<section class="bg-white py-20">
    <style>
        @keyframes mp-vmarquee-up { from { transform: translateY(0); } to { transform: translateY(-50%); } }
        @keyframes mp-vmarquee-down { from { transform: translateY(-50%); } to { transform: translateY(0); } }

        .mp-vmarquee { overflow: hidden; height: 560px; }
        .mp-vmarquee-track { display: flex; flex-direction: column; gap: 24px; will-change: transform; }
        .mp-vmarquee-card { margin-bottom: 0px; }
        .mp-vmarquee-track .mp-vmarquee-card:last-child { margin-bottom: 0; }
        .mp-vmarquee-up { animation: mp-vmarquee-up 14s linear infinite !important; }
        .mp-vmarquee-down { animation: mp-vmarquee-down 16s linear infinite !important; }
        .mp-vmarquee:hover .mp-vmarquee-up, .mp-vmarquee:hover .mp-vmarquee-down { animation-play-state: paused; }
    </style>
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="max-w-md">
                <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-1 py-1 pr-4 text-xs font-semibold text-gray-700 shadow-sm">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-xl bg-[#e11d48]/10 text-[#e11d48]">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="24" height="24" rx="12" fill="#DC325E"/>
                    <path d="M18.3334 12C18.3334 15.4978 15.4979 18.3333 12.0001 18.3333C10.9147 18.3333 9.89301 18.0603 9.00008 17.5791C7.75459 16.908 6.91649 17.5319 6.17736 17.6439C6.06524 17.6609 5.95357 17.6201 5.87339 17.54C5.75169 17.4183 5.72852 17.2301 5.79575 17.0716C6.08585 16.3879 6.35221 15.0921 5.98902 14C5.77995 13.3713 5.66675 12.6989 5.66675 12C5.66675 8.5022 8.50227 5.66667 12.0001 5.66667C15.4979 5.66667 18.3334 8.5022 18.3334 12Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M11.9999 14.6664C11.9999 14.6664 9.33325 13.0193 9.33325 11.4257C9.33325 10.6382 9.89465 9.99976 10.6666 9.99976C11.0666 9.99976 11.4666 10.137 11.9999 10.686C12.5332 10.137 12.9332 9.99976 13.3332 9.99976C14.1052 9.99976 14.6666 10.6382 14.6666 11.4257C14.6666 13.0193 11.9999 14.6664 11.9999 14.6664Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Testominials
            </div>
                <h2 class="mt-4 text-4xl font-extrabold leading-tight tracking-tight text-gray-900">Loved by Marketers.</h2>
                <p class="mt-3 text-sm leading-6 text-gray-500">See what our customers have to say about their experience.</p>
            </div>

            <div class="lg:pl-10">
                @php
                    $mpMarqueeTestimonials = [
                        ['name' => 'Cary', 'role' => 'Manager', 'text' => 'The automation features save us hours every week. We can focus on strategy while the platform handles the execution. Highly recommended'],
                        ['name' => 'Shaina', 'role' => 'Executive', 'text' => 'The automation features save us hours every week. We can focus on strategy while the platform handles the execution. Highly recommended'],
                        ['name' => 'Lemuel', 'role' => 'Developer', 'text' => 'The automation features save us hours every week. We can focus on strategy while the platform handles the execution. Highly recommended'],
                        ['name' => 'Brent', 'role' => 'Strategist', 'text' => 'The automation features save us hours every week. We can focus on strategy while the platform handles the execution. Highly recommended'],
                        ['name' => 'Myron', 'role' => 'Manager', 'text' => 'The automation features save us hours every week. We can focus on strategy while the platform handles the execution. Highly recommended'],
                        ['name' => 'Garnet', 'role' => 'Strategist', 'text' => 'The automation features save us hours every week. We can focus on strategy while the platform handles the execution. Highly recommended'],
                    ];
                    $mpMarqueeLeft = array_slice($mpMarqueeTestimonials, 0, 3);
                    $mpMarqueeRight = array_slice($mpMarqueeTestimonials, 3, 3);
                @endphp

                <div class="relative">
                    <div class="pointer-events-none absolute inset-x-0 top-0 z-10 h-20 bg-gradient-to-b from-white via-white/90 to-transparent"></div>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10 h-20 bg-gradient-to-t from-white via-white/90 to-transparent"></div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="mp-vmarquee">
                            <div class="mp-vmarquee-track mp-vmarquee-up">
                                @foreach($mpMarqueeLeft as $t)
                                    <div class="mp-vmarquee-card mb-6 rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-sm">
                                        <div class="text-xs leading-5 text-gray-500">{{ $t['text'] }}</div>
                                        <div class="mt-6 flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-gray-200 to-gray-100"></div>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900">{{ $t['name'] }}</div>
                                                <div class="text-[11px] text-gray-500">{{ $t['role'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @foreach($mpMarqueeLeft as $t)
                                    <div class="mp-vmarquee-card rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-sm">
                                        <div class="text-xs leading-5 text-gray-500">{{ $t['text'] }}</div>
                                        <div class="mt-6 flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-gray-200 to-gray-100"></div>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900">{{ $t['name'] }}</div>
                                                <div class="text-[11px] text-gray-500">{{ $t['role'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mp-vmarquee sm:pt-10">
                            <div class="mp-vmarquee-track mp-vmarquee-down">
                                @foreach($mpMarqueeRight as $t)
                                    <div class="mp-vmarquee-card rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-sm">
                                        <div class="text-xs leading-5 text-gray-500">{{ $t['text'] }}</div>
                                        <div class="mt-6 flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-gray-200 to-gray-100"></div>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900">{{ $t['name'] }}</div>
                                                <div class="text-[11px] text-gray-500">{{ $t['role'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @foreach($mpMarqueeRight as $t)
                                    <div class="mp-vmarquee-card rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-sm">
                                        <div class="text-xs leading-5 text-gray-500">{{ $t['text'] }}</div>
                                        <div class="mt-6 flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-gray-200 to-gray-100"></div>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-900">{{ $t['name'] }}</div>
                                                <div class="text-[11px] text-gray-500">{{ $t['role'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="pricing" class="relative overflow-hidden bg-gradient-to-b from-[rgba(var(--brand-rgb),0.04)] via-[rgba(var(--brand-rgb),0.03)] to-white py-24">
    <div class="pointer-events-none absolute inset-0 opacity-35" style="background-image: radial-gradient(rgba(var(--brand-rgb),0.10) 1px, transparent 1px); background-size: 34px 34px;"></div>
    <div class="pointer-events-none absolute -left-24 top-20 h-72 w-72 rounded-full bg-[rgba(var(--brand-rgb),0.14)] blur-3xl"></div>
    <div class="pointer-events-none absolute -right-24 bottom-10 h-72 w-72 rounded-full bg-[rgba(var(--brand-rgb),0.12)] blur-3xl"></div>

    <div class="relative mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8" x-data="{ billing: 'monthly' }">
        <div class="mx-auto max-w-2xl text-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white/80 px-1 py-1 pr-3 text-xs font-semibold text-gray-700 shadow-sm backdrop-blur">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.10)] text-[var(--brand-color)]">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="24" height="24" rx="12" fill="var(--brand-color)"/>
                    <path d="M18.3334 12C18.3334 15.4978 15.4979 18.3333 12.0001 18.3333C10.9147 18.3333 9.89301 18.0603 9.00008 17.5791C7.75459 16.908 6.91649 17.5319 6.17736 17.6439C6.06524 17.6609 5.95357 17.6201 5.87339 17.54C5.75169 17.4183 5.72852 17.2301 5.79575 17.0716C6.08585 16.3879 6.35221 15.0921 5.98902 14C5.77995 13.3713 5.66675 12.6989 5.66675 12C5.66675 8.5022 8.50227 5.66667 12.0001 5.66667C15.4979 5.66667 18.3334 8.5022 18.3334 12Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M11.9999 14.6664C11.9999 14.6664 9.33325 13.0193 9.33325 11.4257C9.33325 10.6382 9.89465 9.99976 10.6666 9.99976C11.0666 9.99976 11.4666 10.137 11.9999 10.686C12.5332 10.137 12.9332 9.99976 13.3332 9.99976C14.1052 9.99976 14.6666 10.6382 14.6666 11.4257C14.6666 13.0193 11.9999 14.6664 11.9999 14.6664Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Pricing
            </div>
            <h2 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-gray-900">Curated Pricing Structure</h2>
            <p class="mt-3 text-sm leading-6 text-gray-500">Get real-time insights designed to help you find opportunities faster and close smarter.</p>
        </div>

        <div class="mt-10 flex items-center justify-center">
            <div class="inline-flex items-center rounded-xl border border-gray-200 bg-white/80 p-1 shadow-sm backdrop-blur">
                <button type="button" class="relative rounded-lg px-5 py-2 text-xs font-semibold transition" @click="billing = 'monthly'" :class="billing === 'monthly' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'">Monthly</button>
                <button type="button" class="relative inline-flex items-center gap-2 rounded-lg px-5 py-2 text-xs font-semibold transition" @click="billing = 'yearly'" :class="billing === 'yearly' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                    Yearly
                    <span class="rounded-full bg-[rgba(var(--brand-rgb),0.12)] px-2 py-0.5 text-[10px] font-semibold text-[var(--brand-color)]">Save 16%</span>
                </button>
            </div>
        </div>

        <div class="mt-14 grid gap-6 lg:grid-cols-3">
            @foreach($plans as $plan)
                <div class="relative {{ !empty($plan['featured']) ? 'lg:-mt-6' : '' }}">
                    @if(!empty($plan['featured']))
                        <div class="rounded-3xl bg-[rgba(var(--brand-rgb),0.14)] p-1 shadow-[0_0_0_1px_rgba(var(--brand-rgb),0.25),0_22px_60px_rgba(15,23,42,0.10)] pt-3">
                            <div class="flex items-center justify-center gap-2 px-2 pb-3 pt-1 text-sm font-semibold text-[var(--brand-color)]">
                                <svg class="h-5 w-5 text-[var(--brand-color)]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 3L4 14h7l-1 7 9-11h-7l1-7z"/></svg>
                                <span>Most Recommended</span>
                            </div>

                            <div class="rounded-[22px] border border-[rgba(var(--brand-rgb),0.22)] bg-white p-8 shadow-[0_0_0_1px_rgba(15,23,42,0.04)]">
                                <div class="text-sm font-semibold text-gray-900">{{ $plan['name'] }}</div>
                                <div class="mt-2 text-xs leading-5 text-gray-500">{{ $plan['description'] }}</div>

                                <div class="mt-6 flex items-end gap-1">
                                    <div class="text-5xl font-extrabold tracking-tight text-gray-900">$<span x-text="billing === 'monthly' ? {{ (int)($plan['monthly'] ?? 0) }} : {{ (int)($plan['yearly'] ?? 0) }}"></span></div>
                                    <div class="pb-2 text-sm font-semibold text-gray-500">/mo</div>
                                </div>

                                <a href="#" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-[var(--brand-color)] px-4 py-3 text-xs font-semibold text-white transition hover:brightness-90">
                                    {{ $plan['cta'] ?? 'Try for free' }}
                                </a>

                                <div class="mt-7 space-y-3 text-xs text-gray-600">
                                    @foreach(($plan['features'] ?? []) as $feature)
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-200 bg-white">
                                                <svg class="h-3 w-3 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                            <span>{{ $feature }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-3xl bg-white/60 p-1 shadow-[0_0_0_1px_rgba(15,23,42,0.08),0_18px_50px_rgba(15,23,42,0.06)]">
                            <div class="rounded-[22px] bg-white p-8">
                                <div class="text-sm font-semibold text-gray-900">{{ $plan['name'] }}</div>
                                <div class="mt-2 text-xs leading-5 text-gray-500">{{ $plan['description'] }}</div>

                                <div class="mt-6 flex items-end gap-1">
                                    <div class="text-5xl font-extrabold tracking-tight text-gray-900">$<span x-text="billing === 'monthly' ? {{ (int)($plan['monthly'] ?? 0) }} : {{ (int)($plan['yearly'] ?? 0) }}"></span></div>
                                    <div class="pb-2 text-sm font-semibold text-gray-500">/mo</div>
                                </div>

                                <a href="#" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-[rgba(var(--brand-rgb),0.08)] px-4 py-3 text-xs font-semibold text-[var(--brand-color)] transition hover:bg-[rgba(var(--brand-rgb),0.12)]">
                                    {{ $plan['cta'] ?? 'Try for free' }}
                                </a>

                                <div class="mt-7 space-y-3 text-xs text-gray-600">
                                    @foreach(($plan['features'] ?? []) as $feature)
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-200 bg-white">
                                                <svg class="h-3 w-3 text-gray-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                            <span>{{ $feature }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="bg-gradient-to-b from-[#fff0f3] to-white py-20">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-rose-100 bg-white px-6 py-12 text-center shadow-sm sm:px-10">
            <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white/80 px-1 py-1 pr-3 text-xs font-semibold text-gray-700 shadow-sm backdrop-blur">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-xl bg-[#e11d48]/10 text-[#e11d48]">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="24" height="24" rx="12" fill="#DC325E"/>
                    <path d="M18.3334 12C18.3334 15.4978 15.4979 18.3333 12.0001 18.3333C10.9147 18.3333 9.89301 18.0603 9.00008 17.5791C7.75459 16.908 6.91649 17.5319 6.17736 17.6439C6.06524 17.6609 5.95357 17.6201 5.87339 17.54C5.75169 17.4183 5.72852 17.2301 5.79575 17.0716C6.08585 16.3879 6.35221 15.0921 5.98902 14C5.77995 13.3713 5.66675 12.6989 5.66675 12C5.66675 8.5022 8.50227 5.66667 12.0001 5.66667C15.4979 5.66667 18.3334 8.5022 18.3334 12Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M11.9999 14.6664C11.9999 14.6664 9.33325 13.0193 9.33325 11.4257C9.33325 10.6382 9.89465 9.99976 10.6666 9.99976C11.0666 9.99976 11.4666 10.137 11.9999 10.686C12.5332 10.137 12.9332 9.99976 13.3332 9.99976C14.1052 9.99976 14.6666 10.6382 14.6666 11.4257C14.6666 13.0193 11.9999 14.6664 11.9999 14.6664Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                Take action
            </div>

            <h2 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-gray-900">
                Start Sending <span class="text-[#e11d48]">Better Emails</span> Today.
            </h2>
            <p class="mt-3 text-sm leading-6 text-gray-500">No credit card required. Cancel anytime. Join 10,000+ businesses already growing with EmailFlow.</p>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="#" class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-3 text-xs font-semibold text-white shadow-sm hover:bg-black">Start Free Trial</a>
                <a href="#" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-5 py-3 text-xs font-semibold text-gray-900 shadow-sm hover:bg-gray-100">Book a Live Demo</a>
            </div>
        </div>
    </div>
</section>

<footer class="bg-white pb-12 pt-6">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <a href="#" class="inline-flex items-center gap-2 font-semibold text-gray-900">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#e11d48]/10 text-[#e11d48]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 12.6a8 8 0 1 1-1.7-8.7"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 4v6h-6"/></svg>
                    </span>
                    <span>Logoipsum</span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-6 text-gray-500">Powerful email marketing that grows your business. Create, automate, and analyze with integration</p>
            </div>

            <div>
                <div class="text-sm font-semibold text-gray-900">Product</div>
                <div class="mt-4 space-y-2 text-sm text-gray-500">
                    <a href="#" class="block hover:text-gray-900">Home Page</a>
                    <a href="#" class="block hover:text-gray-900">Templates</a>
                    <a href="#" class="block hover:text-gray-900">Features</a>
                    <a href="#" class="block hover:text-gray-900">Pricing</a>
                    <a href="#" class="block hover:text-gray-900">Integration</a>
                    <a href="#" class="block hover:text-gray-900">API</a>
                </div>
            </div>

            <div>
                <div class="text-sm font-semibold text-gray-900">Resources</div>
                <div class="mt-4 space-y-2 text-sm text-gray-500">
                    <a href="#" class="block hover:text-gray-900">Blog</a>
                    <a href="#" class="block hover:text-gray-900">Help Center</a>
                    <a href="#" class="block hover:text-gray-900">Guides</a>
                    <a href="#" class="block hover:text-gray-900">Webinars</a>
                    <a href="#" class="block hover:text-gray-900">Case Studies</a>
                </div>
            </div>

            <div>
                <div class="text-sm font-semibold text-gray-900">Company</div>
                <div class="mt-4 space-y-2 text-sm text-gray-500">
                    <a href="#" class="block hover:text-gray-900">About Us</a>
                    <a href="#" class="block hover:text-gray-900">Career</a>
                    <a href="#" class="block hover:text-gray-900">Contact</a>
                    <a href="#" class="block hover:text-gray-900">Partners</a>
                    <a href="#" class="block hover:text-gray-900">Press</a>
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-6 text-sm text-gray-400 sm:flex-row">
            <div>© 2024 EmailFlow. All rights reserved.</div>
            <div class="flex items-center gap-2">
                <span>Made with</span>
                <span class="inline-flex h-2 w-2 rounded-full bg-[#e11d48]/60"></span>
                <span>for growing businesses</span>
            </div>
        </div>
    </div>
</footer>

@if(false)
<!-- Hero Section -->
<section class="relative min-h-screen flex items-center overflow-hidden bg-black">
    <!-- Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-black via-gray-900 to-black"></div>
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-20">
        <div class="max-w-4xl mx-auto text-center mb-16">
            <!-- Heading with Gradient Text -->
            <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold mb-6 leading-tight">
                <span class="bg-gradient-to-r from-[#84cc16] via-[#84cc16] to-[#65a30d] bg-clip-text text-transparent">Elevate your email marketing</span>
            </h1>
            
            <!-- Subheading -->
            <p class="text-base sm:text-lg text-gray-400 mb-8 max-w-2xl mx-auto">
                Powerful email marketing platform for modern businesses
            </p>
            
            <!-- CTA Button -->
            <div class="mb-8">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold text-[#0d0d0d] bg-[#84cc16] rounded-lg hover:bg-[#73b512] transition-all duration-300 shadow-lg hover:shadow-xl">
                    Start your free trial
                </a>
            </div>
            
            <!-- Trust Badge -->
            <div class="flex items-center justify-center gap-2 text-sm text-gray-400">
                <span class="font-semibold text-white">Excellent</span>
                <div class="flex gap-0.5">
                    <svg class="w-5 h-5 fill-[#84cc16]" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg class="w-5 h-5 fill-[#84cc16]" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg class="w-5 h-5 fill-[#84cc16]" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg class="w-5 h-5 fill-[#84cc16]" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <svg class="w-5 h-5 fill-[#84cc16]" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <span>4,000+ reviews on</span>
                <svg class="h-4" viewBox="0 0 126 24" fill="none"><path d="M30.4 9.4h-4.8v9.2h-2.4V9.4h-4.8V7.2h12v2.2zm7.6 2.8c0-.8-.2-1.4-.6-1.8-.4-.4-1-.6-1.8-.6s-1.4.2-1.8.6c-.4.4-.6 1-.6 1.8v6h-2.4v-11h2.4v.8c.6-.6 1.4-1 2.4-1 1 0 1.8.3 2.4.9.6.6.9 1.4.9 2.4v7.9H38v-6.1zm8.8-4.8v11h-2.4v-11h2.4zm0-3.2v2.4h-2.4V4.2h2.4zm7.6 14.4c-1 0-1.8-.3-2.4-.9-.6-.6-.9-1.4-.9-2.4v-4.5c0-1 .3-1.8.9-2.4.6-.6 1.4-.9 2.4-.9s1.8.3 2.4.9c.6.6.9 1.4.9 2.4v.8h-2.4v-.8c0-.4-.1-.7-.3-.9-.2-.2-.5-.3-.9-.3s-.7.1-.9.3c-.2.2-.3.5-.3.9v4.5c0 .4.1.7.3.9.2.2.5.3.9.3s.7-.1.9-.3c.2-.2.3-.5.3-.9v-1.2h2.4v1.2c0 1-.3 1.8-.9 2.4-.6.6-1.4.9-2.4.9zm11.2-11v2.2h-2.8v8.8h-2.4V9.8h-2.8V7.6h8zm7.2 0v11h-2.4v-.8c-.6.6-1.4 1-2.4 1-1 0-1.8-.3-2.4-.9-.6-.6-.9-1.4-.9-2.4v-7.9h2.4v7.9c0 .4.1.7.3.9.2.2.5.3.9.3s.7-.1.9-.3c.2-.2.3-.5.3-.9V7.6h2.4zm5.6 11.2c-1 0-1.8-.3-2.4-.9-.6-.6-.9-1.4-.9-2.4v-1.2h2.4v1.2c0 .4.1.7.3.9.2.2.5.3.9.3s.7-.1.9-.3c.2-.2.3-.5.3-.9 0-.4-.1-.7-.3-.9-.2-.2-.5-.4-.9-.6l-1.2-.6c-.6-.3-1.1-.6-1.4-1-.3-.4-.5-1-.5-1.6 0-1 .3-1.8.9-2.4.6-.6 1.4-.9 2.4-.9s1.8.3 2.4.9c.6.6.9 1.4.9 2.4v.8h-2.4v-.8c0-.4-.1-.7-.3-.9-.2-.2-.5-.3-.9-.3s-.7.1-.9.3c-.2.2-.3.5-.3.9 0 .4.1.7.3.9.2.2.5.4.9.6l1.2.6c.6.3 1.1.6 1.4 1 .3.4.5 1 .5 1.6 0 1-.3 1.8-.9 2.4-.6.6-1.4.9-2.4.9zm10.8-11v2.2h-2.8v8.8h-2.4V9.8h-2.8V7.6h8zm8 0v11h-2.4v-.8c-.6.6-1.4 1-2.4 1-1 0-1.8-.3-2.4-.9-.6-.6-.9-1.4-.9-2.4V7.6h2.4v7.9c0 .4.1.7.3.9.2.2.5.3.9.3s.7-.1.9-.3c.2-.2.3-.5.3-.9V7.6h2.4zm5.6 11.2c-1 0-1.8-.3-2.4-.9-.6-.6-.9-1.4-.9-2.4v-4.5c0-1 .3-1.8.9-2.4.6-.6 1.4-.9 2.4-.9s1.8.3 2.4.9c.6.6.9 1.4.9 2.4v.8h-2.4v-.8c0-.4-.1-.7-.3-.9-.2-.2-.5-.3-.9-.3s-.7.1-.9.3c-.2.2-.3.5-.3.9v4.5c0 .4.1.7.3.9.2.2.5.3.9.3s.7-.1.9-.3c.2-.2.3-.5.3-.9v-1.2h2.4v1.2c0 1-.3 1.8-.9 2.4-.6.6-1.4.9-2.4.9z" fill="#fff"/><path d="M8.8 9.2L12 2l3.2 7.2 7.8 1.1-5.6 5.5 1.3 7.8L12 19.8l-6.7 3.8 1.3-7.8L1 10.3l7.8-1.1z" fill="#84cc16"/></svg>
            </div>
        </div>
        
        <!-- Dashboard Preview -->
        <div class="max-w-6xl mx-auto">
            <div class="relative">
                <!-- Glow Effect -->
                <div class="absolute -inset-4 bg-[#84cc16]/10 rounded-3xl blur-3xl"></div>
                
                <!-- Browser Window -->
                <div class="relative bg-[#1a1a1a] rounded-2xl shadow-2xl overflow-hidden border border-gray-800">
                    <!-- Browser Chrome -->
                    <div class="flex items-center gap-2 px-4 py-3 bg-[#2a2a2a] border-b border-gray-700">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                            <div class="flex items-center gap-2 px-4 py-1.5 bg-[#1a1a1a] rounded-lg border border-gray-600 text-xs text-gray-400 max-w-md w-full">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>yourcompany.mailzen.app</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                        </div>
                    </div>
                    
                    <!-- Dashboard Content -->
                    <div class="bg-[#0d0d0d] p-6">
                        <!-- Top Navigation -->
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-800">
                            <div class="flex items-center gap-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-[var(--brand-color)] to-[rgba(var(--brand-rgb),0.75)] rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <span class="text-white font-semibold">MailZen</span>
                                </div>
                                <nav class="flex items-center gap-6">
                                    <a href="#" class="text-[var(--brand-color)] font-medium">Analytics</a>
                                    <a href="#" class="text-gray-400 hover:text-white transition-colors">Campaigns</a>
                                    <a href="#" class="text-gray-400 hover:text-white transition-colors">Lists</a>
                                    <a href="#" class="text-gray-400 hover:text-white transition-colors">Reports</a>
                                </nav>
                            </div>
                            <div class="flex items-center gap-3">
                                <button class="p-2 text-gray-400 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </button>
                                <div class="w-8 h-8 bg-gradient-to-br from-[var(--brand-color)] to-[rgba(var(--brand-rgb),0.65)] rounded-full"></div>
                            </div>
                        </div>
                        
                        <!-- Analytics Dashboard -->
                        <div class="grid grid-cols-12 gap-6">
                            <!-- Left Column - Stats -->
                            <div class="col-span-3 space-y-4">
                                <div class="bg-[#1a1a1a] rounded-lg p-4 border border-gray-800">
                                    <div class="text-gray-400 text-xs uppercase tracking-wider mb-2">Total Revenue</div>
                                    <div class="text-2xl font-bold text-white">$84,254</div>
                                    <div class="flex items-center gap-1 mt-2">
                                        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        <span class="text-green-500 text-sm">+12.5%</span>
                                    </div>
                                </div>
                                
                                <div class="bg-[#1a1a1a] rounded-lg p-4 border border-gray-800">
                                    <div class="text-gray-400 text-xs uppercase tracking-wider mb-2">Active Users</div>
                                    <div class="text-2xl font-bold text-white">2,845</div>
                                    <div class="flex items-center gap-1 mt-2">
                                        <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        <span class="text-green-500 text-sm">+8.2%</span>
                                    </div>
                                </div>
                                
                                <div class="bg-[#1a1a1a] rounded-lg p-4 border border-gray-800">
                                    <div class="text-gray-400 text-xs uppercase tracking-wider mb-2">Conversion</div>
                                    <div class="text-2xl font-bold text-white">3.24%</div>
                                    <div class="flex items-center gap-1 mt-2">
                                        <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                                        <span class="text-red-500 text-sm">-2.1%</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Center Column - Line Chart -->
                            <div class="col-span-6">
                                <div class="bg-[#1a1a1a] rounded-lg p-6 border border-gray-800">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-white font-semibold">Revenue Overview</h3>
                                        <select class="bg-[#2a2a2a] text-gray-300 text-sm px-3 py-1 rounded border border-gray-700">
                                            <option>Last 7 days</option>
                                            <option>Last 30 days</option>
                                            <option>Last 90 days</option>
                                        </select>
                                    </div>
                                    <!-- Line Chart Area -->
                                    <div class="h-64 relative">
                                        <!-- Grid Lines -->
                                        <div class="absolute inset-0 flex flex-col justify-between">
                                            <div class="border-t border-gray-800"></div>
                                            <div class="border-t border-gray-800"></div>
                                            <div class="border-t border-gray-800"></div>
                                            <div class="border-t border-gray-800"></div>
                                            <div class="border-t border-gray-800"></div>
                                        </div>
                                        <!-- Line Chart -->
                                        <svg class="w-full h-full" viewBox="0 0 600 256">
                                            <!-- Line -->
                                            <path d="M 50 200 Q 100 180, 150 160 T 250 140 Q 300 130, 350 100 T 450 80 Q 500 70, 550 60" 
                                                  stroke="#84cc16" stroke-width="3" fill="none"/>
                                            <!-- Area under line -->
                                            <path d="M 50 200 Q 100 180, 150 160 T 250 140 Q 300 130, 350 100 T 450 80 Q 500 70, 550 60 L 550 256 L 50 256 Z" 
                                                  fill="url(#gradient)" opacity="0.1"/>
                                            <!-- Gradient Definition -->
                                            <defs>
                                                <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                                    <stop offset="0%" style="stop-color:#84cc16;stop-opacity:0.5" />
                                                    <stop offset="100%" style="stop-color:#84cc16;stop-opacity:0" />
                                                </linearGradient>
                                            </defs>
                                            <!-- Data Points -->
                                            <circle cx="50" cy="200" r="4" fill="#84cc16"/>
                                            <circle cx="150" cy="160" r="4" fill="#84cc16"/>
                                            <circle cx="250" cy="140" r="4" fill="#84cc16"/>
                                            <circle cx="350" cy="100" r="4" fill="#84cc16"/>
                                            <circle cx="450" cy="80" r="4" fill="#84cc16"/>
                                            <circle cx="550" cy="60" r="4" fill="#84cc16"/>
                                        </svg>
                                    </div>
                                    <div class="flex justify-between mt-2 text-xs text-gray-500">
                                        <span>Mon</span>
                                        <span>Tue</span>
                                        <span>Wed</span>
                                        <span>Thu</span>
                                        <span>Fri</span>
                                        <span>Sat</span>
                                        <span>Sun</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column - Campaign Stats -->
                            <div class="col-span-3 space-y-4">
                                <div class="bg-[#1a1a1a] rounded-lg p-4 border border-gray-800">
                                    <h3 class="text-white font-semibold mb-4">Campaign Stats</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-400 text-sm">Sent</span>
                                            <span class="text-white font-medium">12,453</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-400 text-sm">Delivered</span>
                                            <span class="text-white font-medium">11,892</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-400 text-sm">Opened</span>
                                            <span class="text-white font-medium">8,234</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-400 text-sm">Clicked</span>
                                            <span class="text-white font-medium">1,245</span>
                                        </div>
                                        <div class="h-px bg-gray-800 my-3"></div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-400 text-sm">Open Rate</span>
                                            <span class="text-green-400 font-medium">69.2%</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-400 text-sm">Click Rate</span>
                                            <span class="text-green-400 font-medium">10.5%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Logos/Trusted By Section -->
<section class="py-16 bg-white border-b border-gray-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm font-medium text-gray-500 uppercase tracking-wider mb-8">Trusted by innovative companies worldwide</p>
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16 opacity-60">
            @for($i = 1; $i <= 6; $i++)
            <div class="flex items-center justify-center h-8 grayscale hover:grayscale-0 transition-all duration-300">
                <div class="h-full w-28 rounded bg-gray-200/70"></div>
            </div>
            @endfor
        </div>
    </div>
</section>


<!-- Features Section -->
<section id="features" class="py-24 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Everything you need to succeed</h2>
            <p class="text-lg text-gray-600">everything in between from within Lexend.</p>
        </div>
        
        <!-- Features Grid -->
        <div class="grid md:grid-cols-3 gap-8 max-w-7xl mx-auto">
            <!-- Feature 1: Integrations -->
            <div class="group relative bg-gradient-to-br from-gray-50 to-white rounded-3xl p-8 border border-gray-200 hover:shadow-xl transition-all duration-300">
                <!-- Browser Window Mockup -->
                <div class="mb-6">
                    <div class="bg-[#0a3d3d] rounded-xl overflow-hidden shadow-lg">
                        <!-- Browser Chrome -->
                        <div class="flex items-center gap-1.5 px-3 py-2 border-b border-white/10">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 bg-white">
                            <div class="text-xs text-gray-500 mb-3">1000+ Apps & Integrations</div>
                            <div class="text-lg font-bold text-gray-900 mb-4">My apps</div>
                            
                            <!-- App List -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                                            <div class="w-4 h-4 bg-red-500 rounded"></div>
                                        </div>
                                        <span class="font-medium text-sm">Asana</span>
                                    </div>
                                    <div class="w-6 h-6 rounded-full border-2 border-[#84cc16] flex items-center justify-center">
                                        <div class="w-2 h-2 bg-[#84cc16] rounded-full"></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center">
                                            <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                                        </div>
                                        <span class="font-medium text-sm">Mailchimp</span>
                                    </div>
                                    <div class="w-6 h-6 rounded-full border-2 border-[#84cc16] flex items-center justify-center">
                                        <div class="w-2 h-2 bg-[#84cc16] rounded-full"></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                                            <div class="w-4 h-4 bg-orange-500 rounded"></div>
                                        </div>
                                        <span class="font-medium text-sm">Zapier</span>
                                    </div>
                                    <div class="w-6 h-6 rounded-full border-2 border-[#84cc16] flex items-center justify-center">
                                        <div class="w-2 h-2 bg-[#84cc16] rounded-full"></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                        </div>
                                        <span class="font-medium text-sm">Drive</span>
                                    </div>
                                    <div class="w-6 h-6 rounded-full border-2 border-[#84cc16] flex items-center justify-center">
                                        <div class="w-2 h-2 bg-[#84cc16] rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 mb-3">Seamless integrations with your existing tools</h3>
                
                <a href="#" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#84cc16] text-[#0a3d3d] font-semibold rounded-lg hover:bg-[#73b512] transition-colors">
                    Try it now
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            
            <!-- Feature 2: Dashboard -->
            <div class="group relative bg-gradient-to-br from-gray-50 to-white rounded-3xl p-8 border border-gray-200 hover:shadow-xl transition-all duration-300">
                <!-- Browser Window Mockup -->
                <div class="mb-6">
                    <div class="bg-[#0a3d3d] rounded-xl overflow-hidden shadow-lg">
                        <!-- Browser Chrome -->
                        <div class="flex items-center gap-1.5 px-3 py-2 border-b border-white/10">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 bg-white">
                            <div class="text-xs text-gray-500 mb-3">Most Growing Businesses</div>
                            <div class="text-lg font-bold text-gray-900 mb-4">Scalable plans</div>
                            
                            <!-- Donut Chart -->
                            <div class="relative w-full aspect-square max-w-[200px] mx-auto">
                                <svg viewBox="0 0 100 100" class="transform -rotate-90">
                                    <!-- Background circle -->
                                    <circle cx="50" cy="50" r="35" fill="none" stroke="#f3f4f6" stroke-width="15"/>
                                    <!-- Green segment (50%) -->
                                    <circle cx="50" cy="50" r="35" fill="none" stroke="#84cc16" stroke-width="15" stroke-dasharray="110 220" stroke-dashoffset="0"/>
                                    <!-- Teal segment (30%) -->
                                    <circle cx="50" cy="50" r="35" fill="none" stroke="#0a3d3d" stroke-width="15" stroke-dasharray="66 220" stroke-dashoffset="-110"/>
                                    <!-- Pink segment (20%) -->
                                    <circle cx="50" cy="50" r="35" fill="none" stroke="#fecaca" stroke-width="15" stroke-dasharray="44 220" stroke-dashoffset="-176"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-xs text-gray-500">Total</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Legend -->
                            <div class="flex justify-center gap-4 mt-4 text-xs">
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-[#84cc16]"></div>
                                    <span class="text-gray-600">Value 1</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-[#0a3d3d]"></div>
                                    <span class="text-gray-600">Value 2</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-[#fecaca]"></div>
                                    <span class="text-gray-600">Value 3</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 mb-3">Intuitive dashboard for at-a-glance insights</h3>
                
                <a href="#" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#84cc16] text-[#0a3d3d] font-semibold rounded-lg hover:bg-[#73b512] transition-colors">
                    Try it now
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            
            <!-- Feature 3: Reports -->
            <div class="group relative bg-gradient-to-br from-gray-50 to-white rounded-3xl p-8 border border-gray-200 hover:shadow-xl transition-all duration-300">
                <!-- Browser Window Mockup -->
                <div class="mb-6">
                    <div class="bg-[#0a3d3d] rounded-xl overflow-hidden shadow-lg">
                        <!-- Browser Chrome -->
                        <div class="flex items-center gap-1.5 px-3 py-2 border-b border-white/10">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 bg-white">
                            <div class="text-xs text-gray-500 mb-3">Project Reports</div>
                            <div class="text-lg font-bold text-gray-900 mb-4">Today's reports</div>
                            
                            <!-- Bar Chart -->
                            <div class="flex items-end justify-between h-32 gap-1">
                                @for($i = 0; $i < 12; $i++)
                                <div class="flex-1 flex flex-col justify-end gap-0.5">
                                    <div class="w-full bg-[#84cc16] rounded-t" style="height: {{ rand(40, 90) }}%"></div>
                                    <div class="w-full bg-[#0a3d3d] rounded-b" style="height: {{ rand(30, 60) }}%"></div>
                                </div>
                                @endfor
                            </div>
                            
                            <!-- Legend -->
                            <div class="flex justify-center gap-4 mt-4 text-xs">
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-[#84cc16]"></div>
                                    <span class="text-gray-600">Series 1</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-[#0a3d3d]"></div>
                                    <span class="text-gray-600">Series 2</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-gray-200"></div>
                                    <span class="text-gray-600">Series 3</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 mb-3">Automated data analysis and reporting</h3>
                
                <a href="#" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[var(--brand-color)] text-white font-semibold rounded-lg hover:brightness-90 transition-colors">
                    Try it now
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-24 bg-slate-900 text-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <span class="inline-block px-4 py-1.5 text-sm font-semibold text-[rgba(var(--brand-rgb),0.85)] bg-[rgba(var(--brand-rgb),0.20)] rounded-full mb-4">How It Works</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6">Get started in minutes</h2>
            <p class="text-lg text-gray-400">Three simple steps to transform your email marketing strategy.</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
            @php
            $steps = [
                ['number' => '01', 'title' => 'Install & Configure', 'description' => 'Deploy on your server with our one-click installer or Docker container. Configure your SMTP settings.'],
                ['number' => '02', 'title' => 'Import & Segment', 'description' => 'Import your subscriber lists, create segments, and set up your audience targeting criteria.'],
                ['number' => '03', 'title' => 'Create & Launch', 'description' => 'Design beautiful emails, set up automation workflows, and launch your campaigns.']
            ];
            @endphp
            
            @foreach($steps as $step)
            <div class="relative group">
                <div class="text-7xl font-bold text-[rgba(var(--brand-rgb),0.20)] mb-4">{{ $step['number'] }}</div>
                <h3 class="text-2xl font-bold mb-4">{{ $step['title'] }}</h3>
                <p class="text-gray-400 leading-relaxed">{{ $step['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Integrations Section -->
<section class="py-24 bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <span class="inline-block px-4 py-1.5 text-sm font-semibold text-[var(--brand-color)] bg-[rgba(var(--brand-rgb),0.10)] rounded-full mb-4">Integrations</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6">Connect with your favorite tools</h2>
                <p class="text-lg text-gray-600 mb-8">Seamlessly integrate with the tools you already use. Our platform works with 100+ popular services out of the box.</p>
                
                <div class="grid grid-cols-3 gap-4 mb-8">
                    @foreach($integrations as $integration)
                    <div class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-[rgba(var(--brand-rgb),0.35)] hover:shadow-md transition-all duration-300">
                        <div class="w-10 h-10 rounded-lg bg-[rgba(var(--brand-rgb),0.10)] flex items-center justify-center text-[var(--brand-color)]">
                            <i data-lucide="{{ $integration['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <span class="font-medium text-gray-900">{{ $integration['name'] }}</span>
                    </div>
                    @endforeach
                </div>
                
                <a href="#" class="inline-flex items-center text-[var(--brand-color)] font-semibold hover:brightness-90 group">
                    View all integrations
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
            
            <div class="relative">
                <div class="absolute -inset-4 bg-gradient-to-r from-[rgba(var(--brand-rgb),0.45)] to-[rgba(var(--brand-rgb),0.20)] rounded-2xl blur-2xl opacity-20"></div>
                <div class="relative rounded-2xl shadow-xl w-full aspect-[6/5] bg-white border border-gray-200 flex items-center justify-center text-sm text-gray-400">Image Placeholder</div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-24 bg-white overflow-hidden">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <span class="inline-block px-4 py-1.5 text-sm font-semibold text-[var(--brand-color)] bg-[rgba(var(--brand-rgb),0.10)] rounded-full mb-4">Testimonials</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">Loved by marketers worldwide</h2>
            <p class="text-lg text-gray-600">See what our customers have to say about their experience.</p>
        </div>
        
        <!-- Sliding Testimonials -->
        <div class="relative">
            <!-- Gradient Overlays -->
            <div class="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-white via-white/90 to-transparent z-10 pointer-events-none"></div>
            <div class="absolute right-0 top-0 bottom-0 w-32 bg-gradient-to-l from-white via-white/90 to-transparent z-10 pointer-events-none"></div>
            
            <!-- Track -->
            <div class="flex gap-8 animate-scroll" style="animation: scroll 30s linear infinite;">
                <!-- First Set -->
                @foreach($testimonials as $testimonial)
                <div class="flex-none w-80 group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-[rgba(var(--brand-rgb),0.35)] hover:shadow-xl transition-all duration-300">
                    <!-- Quote Icon -->
                    <div class="absolute top-6 right-6 text-[rgba(var(--brand-rgb),0.25)]">
                        <i data-lucide="quote" class="w-10 h-10"></i>
                    </div>
                    
                    <!-- Stars -->
                    <div class="flex gap-1 mb-6">
                        @for($i = 0; $i < 5; $i++)
                        <i data-lucide="star" class="w-5 h-5 fill-yellow-400 text-yellow-400"></i>
                        @endfor
                    </div>
                    
                    <!-- Quote -->
                    <p class="text-gray-700 leading-relaxed mb-8">"{{ $testimonial['quote'] }}"</p>
                    
                    <!-- Author -->
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[var(--brand-color)] to-[rgba(var(--brand-rgb),0.65)] flex items-center justify-center text-white font-bold">
                            {{ substr($testimonial['author'], 0, 1) }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $testimonial['author'] }}</div>
                            <div class="text-sm text-gray-500">{{ $testimonial['role'] }}, {{ $testimonial['company'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <!-- Duplicate Set for Seamless Loop -->
                @foreach($testimonials as $testimonial)
                <div class="flex-none w-80 group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-[rgba(var(--brand-rgb),0.35)] hover:shadow-xl transition-all duration-300">
                    <!-- Quote Icon -->
                    <div class="absolute top-6 right-6 text-[rgba(var(--brand-rgb),0.25)]">
                        <i data-lucide="quote" class="w-10 h-10"></i>
                    </div>
                    
                    <!-- Stars -->
                    <div class="flex gap-1 mb-6">
                        @for($i = 0; $i < 5; $i++)
                        <i data-lucide="star" class="w-5 h-5 fill-yellow-400 text-yellow-400"></i>
                        @endfor
                    </div>
                    
                    <!-- Quote -->
                    <p class="text-gray-700 leading-relaxed mb-8">"{{ $testimonial['quote'] }}"</p>
                    
                    <!-- Author -->
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[var(--brand-color)] to-[rgba(var(--brand-rgb),0.65)] flex items-center justify-center text-white font-bold">
                            {{ substr($testimonial['author'], 0, 1) }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $testimonial['author'] }}</div>
                            <div class="text-sm text-gray-500">{{ $testimonial['role'] }}, {{ $testimonial['company'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Custom CSS for Animation -->
    <style>
        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }
        
        .animate-scroll {
            display: flex;
        }
        
        .animate-scroll:hover {
            animation-play-state: paused;
        }
    </style>
</section>

@if(false)
<!-- Pricing Section -->
<section id="pricing" class="py-24 bg-[#f5f1e8]">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-12">
            <div class="inline-block px-4 py-1.5 text-sm font-medium text-gray-600 bg-white rounded-full mb-6">Pricing</div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Affordable prices and scalable plans <span class="text-[#0a3d3d]">to fit any business size</span>
            </h2>
        </div>
        
        <!-- Toggle -->
        <div class="flex items-center justify-center gap-3 mb-12" x-data="{ billingPeriod: 'monthly' }">
            <button 
                @click="billingPeriod = 'monthly'"
                :class="billingPeriod === 'monthly' ? 'bg-[#84cc16] text-[#0a3d3d]' : 'bg-white text-gray-700'"
                class="px-6 py-2.5 rounded-lg font-semibold transition-all duration-300"
            >
                Pay monthly
            </button>
            <button 
                @click="billingPeriod = 'yearly'"
                :class="billingPeriod === 'yearly' ? 'bg-[#84cc16] text-[#0a3d3d]' : 'bg-white text-gray-700'"
                class="px-6 py-2.5 rounded-lg font-semibold transition-all duration-300"
            >
                Pay yearly
            </button>
        </div>
        
        <!-- Pricing Cards -->
        <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto">
            <!-- Starter Plan -->
            <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all duration-300">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Starter</h3>
                <p class="text-sm text-gray-600 mb-6">For individuals, freelancers</p>
                
                <div class="mb-6">
                    <span class="text-5xl font-bold text-gray-900">$49</span>
                    <span class="text-gray-500 text-sm">/ month</span>
                    <div class="text-xs text-gray-500 mt-1">Billed once monthly</div>
                </div>
                
                <div class="mb-8">
                    <div class="font-semibold text-gray-900 mb-4">Standout features</div>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Fast and Reliable</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Discover Data Everywhere</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span class="text-sm text-gray-400">Enrich Data with Context</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span class="text-sm text-gray-400">Risk Management</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span class="text-sm text-gray-400">Privacy Compliance</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span class="text-sm text-gray-400">Third-Party Management</span>
                        </li>
                    </ul>
                </div>
                
                <button class="w-full py-3 px-6 bg-gray-100 text-gray-900 font-semibold rounded-lg hover:bg-gray-200 transition-colors mb-2">
                    Try for free
                </button>
                <p class="text-xs text-center text-gray-500">No credit card required!</p>
            </div>
            
            <!-- Pro Plan (Popular) -->
            <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-[#84cc16]">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Pro</h3>
                <p class="text-sm text-gray-600 mb-6">For startups, agencies</p>
                
                <div class="mb-6">
                    <span class="text-5xl font-bold text-gray-900">$89</span>
                    <span class="text-gray-500 text-sm">/ month</span>
                    <div class="text-xs text-gray-500 mt-1">Billed once monthly</div>
                </div>
                
                <div class="mb-8">
                    <div class="font-semibold text-gray-900 mb-4">Standout features</div>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Fast and Reliable</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Discover Data Everywhere</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Enrich Data with Context</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Risk Management</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span class="text-sm text-gray-400">Privacy Compliance</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span class="text-sm text-gray-400">Third-Party Management</span>
                        </li>
                    </ul>
                </div>
                
                <button class="w-full py-3 px-6 bg-[#84cc16] text-[#0a3d3d] font-semibold rounded-lg hover:bg-[#73b512] transition-colors mb-2">
                    Try for free
                </button>
                <p class="text-xs text-center text-gray-500">No credit card required!</p>
            </div>
            
            <!-- Business Plan -->
            <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all duration-300">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Business</h3>
                <p class="text-sm text-gray-600 mb-6">For large business, companies</p>
                
                <div class="mb-6">
                    <span class="text-5xl font-bold text-gray-900">$249</span>
                    <span class="text-gray-500 text-sm">/ month</span>
                    <div class="text-xs text-gray-500 mt-1">Billed once monthly</div>
                </div>
                
                <div class="mb-8">
                    <div class="font-semibold text-gray-900 mb-4">Standout features</div>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Fast and Reliable</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Discover Data Everywhere</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Enrich Data with Context</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Risk Management</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Privacy Compliance</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#84cc16] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-gray-700">Third-Party Management</span>
                        </li>
                    </ul>
                </div>
                
                <button class="w-full py-3 px-6 bg-gray-100 text-gray-900 font-semibold rounded-lg hover:bg-gray-200 transition-colors mb-2">
                    Get in touch
                </button>
                <p class="text-xs text-center text-gray-500 invisible">Placeholder</p>
            </div>
        </div>
    </div>
</section>
@endif

<!-- FAQ Section -->
<section class="py-24 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-1.5 text-sm font-semibold text-[var(--brand-color)] bg-[rgba(var(--brand-rgb),0.10)] rounded-full mb-4">FAQ</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6">Frequently asked questions</h2>
                <p class="text-lg text-gray-600">Everything you need to know about the platform.</p>
            </div>
            
            <div class="space-y-4" x-data="{ openFaq: null }">
                @foreach($faqs as $index => $faq)
                <div class="border border-gray-200 rounded-xl overflow-hidden hover:border-[rgba(var(--brand-rgb),0.35)] transition-colors">
                    <button 
                        @click="openFaq = openFaq === {{ $index }} ? null : {{ $index }}"
                        class="flex items-center justify-between w-full p-6 text-left bg-white hover:bg-gray-50 transition-colors"
                    >
                        <span class="font-semibold text-gray-900 pr-4">{{ $faq['question'] }}</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-gray-500 transition-transform duration-300" :class="{ 'rotate-180': openFaq === {{ $index }} }"></i>
                    </button>
                    <div 
                        x-show="openFaq === {{ $index }}"
                        x-collapse
                        class="px-6 pb-6"
                    >
                        <p class="text-gray-600 leading-relaxed">{{ $faq['answer'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-24 bg-gradient-to-br from-[rgba(var(--brand-rgb),0.85)] via-[rgba(var(--brand-rgb),0.75)] to-[rgba(var(--brand-rgb),0.90)] relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiLz48L2c+PC9zdmc+')]"></div>
    </div>
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">Ready to transform your email marketing?</h2>
            <p class="text-xl text-white/80 mb-10">Join thousands of marketers who have already made the switch. Start your free trial today.</p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-[var(--brand-color)] bg-white rounded-xl hover:bg-gray-100 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                    Start Free Trial
                    <i data-lucide="arrow-right" class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="#" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white border-2 border-white/30 rounded-xl hover:bg-white/10 hover:border-white/50 transition-all duration-300">
                    Contact Sales
                </a>
            </div>
            
            <p class="mt-8 text-white/60 text-sm">No credit card required. 14-day free trial.</p>
        </div>
    </div>
</section>
@endif
@endsection

@push('styles')
<style>
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out forwards;
        opacity: 0;
    }

    @keyframes mp-vmarquee-up {
        from { transform: translateY(0); }
        to { transform: translateY(-50%); }
    }

    @keyframes mp-vmarquee-down {
        from { transform: translateY(-50%); }
        to { transform: translateY(0); }
    }

    .mp-vmarquee {
        overflow: hidden;
        height: 560px;
    }

    .mp-vmarquee-track {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        will-change: transform;
    }

    .mp-vmarquee-up {
        animation: mp-vmarquee-up 24s linear infinite;
    }

    .mp-vmarquee-down {
        animation: mp-vmarquee-down 26s linear infinite;
    }

    .mp-vmarquee:hover .mp-vmarquee-up,
    .mp-vmarquee:hover .mp-vmarquee-down {
        animation-play-state: paused;
    }

    @media (prefers-reduced-motion: reduce) {
        .mp-vmarquee {
            height: auto;
        }
        .mp-vmarquee-up,
        .mp-vmarquee-down {
            animation: none !important;
            transform: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script>
    (function () {
        function splitWords(root) {
            if (!root || root.dataset.mpGsapSplit === '1') return;

            var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
                acceptNode: function (node) {
                    if (!node || !node.nodeValue) return NodeFilter.FILTER_REJECT;
                    if (!node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
                    return NodeFilter.FILTER_ACCEPT;
                }
            });

            var nodes = [];
            while (walker.nextNode()) nodes.push(walker.currentNode);

            nodes.forEach(function (textNode) {
                var parts = textNode.nodeValue.split(/(\s+)/);
                var frag = document.createDocumentFragment();

                parts.forEach(function (part) {
                    if (!part) return;
                    if (/^\s+$/.test(part)) {
                        frag.appendChild(document.createTextNode(' '));
                        return;
                    }
                    var sp = document.createElement('span');
                    sp.setAttribute('data-mp-word', '1');
                    sp.style.display = 'inline-block';
                    sp.textContent = part;
                    frag.appendChild(sp);
                });

                textNode.parentNode.replaceChild(frag, textNode);
            });

            root.dataset.mpGsapSplit = '1';
        }

        function animateWordsOnce(root) {
            if (!root) return null;
            if (root.dataset.mpGsapAnimated === '1') return null;
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return null;
            if (!window.gsap) return null;

            splitWords(root);

            var words = root.querySelectorAll('[data-mp-word="1"]');
            if (!words || !words.length) return null;

            root.dataset.mpGsapAnimated = '1';

            var tl = window.gsap.timeline();
            tl.set(words, { opacity: 0, y: 18, filter: 'blur(4px)' });
            tl.to(words, {
                opacity: 1,
                y: 0,
                filter: 'blur(0px)',
                duration: 0.7,
                ease: 'power3.out',
                stagger: 0.04
            });
            return tl;
        }

        function animateHeroHeadline() {
            var root = document.querySelector('[data-mp-gsap-words="hero-headline"]');
            if (!root) return;

            if (window.__mpHeroHeadlineTl) {
                try { window.__mpHeroHeadlineTl.kill(); } catch (e) {}
            }

            root.dataset.mpGsapAnimated = '0';
            var tl = animateWordsOnce(root);
            if (!tl) return;

            var imgs = document.querySelectorAll('[data-mp-gsap-float="hero"]');
            if (imgs && imgs.length) {
                tl.fromTo(imgs, {
                    opacity: 0,
                    y: 24,
                    filter: 'blur(10px)'
                }, {
                    opacity: 1,
                    y: 0,
                    filter: 'blur(0px)',
                    duration: 0.75,
                    ease: 'power3.out',
                    stagger: 0.12
                }, '>-0.15');

                imgs.forEach(function (img) {
                    var i = Number(img.getAttribute('data-mp-gsap-float-i') || '0') || 0;
                    window.gsap.to(img, {
                        y: '+=10',
                        duration: 3 + (i * 0.25),
                        repeat: -1,
                        yoyo: true,
                        ease: 'sine.inOut',
                        delay: 0.6 + (i * 0.15)
                    });
                });
            }

            window.__mpHeroHeadlineTl = tl;
        }

        function setupWordAnimations() {
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            if (!window.gsap) return;

            var els = document.querySelectorAll('[data-mp-gsap-words], .mp-gsap-words');
            if (!els || !els.length) return;

            els.forEach(function (el) {
                el.dataset.mpGsapObserved = '0';
                el.dataset.mpGsapInviewRunning = '0';
                if (String(el.getAttribute('data-mp-gsap-words') || '') !== 'hero-headline') {
                    el.dataset.mpGsapAnimated = '0';
                }
            });

            if (!('IntersectionObserver' in window)) {
                els.forEach(function (el) {
                    var key = String(el.getAttribute('data-mp-gsap-words') || '');
                    if (key === 'hero-headline') animateHeroHeadline();
                    else animateWordsOnce(el);
                });
                return;
            }

            if (window.__mpWordsIO) {
                try { window.__mpWordsIO.disconnect(); } catch (e) {}
                window.__mpWordsIO = null;
            }

            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    var el = entry.target;
                    if (el.dataset.mpGsapInviewRunning === '1') return;
                    el.dataset.mpGsapInviewRunning = '1';
                    var key = String(el.getAttribute('data-mp-gsap-words') || '');

                    try { io.unobserve(el); } catch (e) {}

                    if (key === 'hero-headline') {
                        animateHeroHeadline();
                    } else {
                        animateWordsOnce(el);
                    }
                });
            }, { root: null, threshold: 0.25 });

            window.__mpWordsIO = io;

            els.forEach(function (el) {
                el.dataset.mpGsapObserved = '1';
                io.observe(el);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupWordAnimations);
        } else {
            setupWordAnimations();
        }
        document.addEventListener('turbo:load', setupWordAnimations);
    })();
</script>
@endpush
