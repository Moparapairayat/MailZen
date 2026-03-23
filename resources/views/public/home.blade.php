@extends('layouts.public')

@section('title', 'Self-Hosted Email Marketing Platform')
@section('pageId', 'home-1')

@section('content')
@php
    try {
        $heroDescription = (string) \App\Models\Setting::get('home_1_hero_description', 'Host it yourself, run it as SaaS, or manage clients. MailZen gives you complete control over your email infrastructure with enterprise-grade features.');
        $heroScrollText = (string) \App\Models\Setting::get('home_1_hero_scroll_text', '');
        $heroButtonText = (string) \App\Models\Setting::get('home_1_hero_button_text', 'Get Started Free');
        $heroButtonType = (string) \App\Models\Setting::get('home_1_hero_button_type', 'link');
        $heroButtonUrl = (string) \App\Models\Setting::get('home_1_hero_button_url', route('register'));
        $heroImagePath = (string) \App\Models\Setting::get('home_1_hero_image', '');
    } catch (\Throwable $e) {
        $heroDescription = 'Host it yourself, run it as SaaS, or manage clients. MailZen gives you complete control over your email infrastructure with enterprise-grade features.';
        $heroScrollText = '';
        $heroButtonText = 'Get Started Free';
        $heroButtonType = 'link';
        $heroButtonUrl = route('register');
        $heroImagePath = '';
    }

    $brandingDisk = (string) config('filesystems.branding_disk', 'public');
    $heroImageUrl = (is_string($heroImagePath) && trim($heroImagePath) !== '')
        ? \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($heroImagePath)
        : null;

    $heroButtonUrl = is_string($heroButtonUrl) && trim($heroButtonUrl) !== '' ? $heroButtonUrl : route('register');
    $heroButtonType = in_array($heroButtonType, ['link', 'video'], true) ? $heroButtonType : 'link';

    try {
        $logoPaths = \App\Models\Setting::get('home_1_logos', []);
    } catch (\Throwable $e) {
        $logoPaths = [];
    }

    $logoPaths = is_array($logoPaths) ? $logoPaths : [];
    $logoUrls = [];
    foreach ($logoPaths as $p) {
        if (!is_string($p) || trim($p) === '') {
            continue;
        }
        $logoUrls[] = \Illuminate\Support\Facades\Storage::disk($brandingDisk)->url($p);
    }

    try {
        $featuresTitle = (string) \App\Models\Setting::get('home_1_features_title', 'Everything you need to run email marketing at scale');
        $featuresSubtitle = (string) \App\Models\Setting::get('home_1_features_subtitle', "Whether you're sending for yourself or running a full SaaS business, MailZen has you covered.");
        $features1Title = (string) \App\Models\Setting::get('home_1_features_1_title', 'Multi-Tenant SaaS Ready');
        $features1Description = (string) \App\Models\Setting::get('home_1_features_1_description', 'Run your own email marketing SaaS. Manage customers, plans, billing, and permissions from a powerful admin panel.');
        $features2Title = (string) \App\Models\Setting::get('home_1_features_2_title', 'Campaigns & Automation');
        $features2Description = (string) \App\Models\Setting::get('home_1_features_2_description', 'Create one-time campaigns, recurring sends, or automated drip sequences. Drag-and-drop editor with responsive templates.');
        $features3Title = (string) \App\Models\Setting::get('home_1_features_3_title', 'List Management');
        $features3Description = (string) \App\Models\Setting::get('home_1_features_3_description', 'Unlimited lists with custom fields, tags, and segments. Import/export CSV, double opt-in, and GDPR compliance built-in.');
        $features4Title = (string) \App\Models\Setting::get('home_1_features_4_title', 'Multiple Delivery Servers');
        $features4Description = (string) \App\Models\Setting::get('home_1_features_4_description', 'Connect Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, or any SMTP. Load balance and rotate for maximum deliverability.');
        $features5Title = (string) \App\Models\Setting::get('home_1_features_5_title', 'Real-Time Analytics');
        $features5Description = (string) \App\Models\Setting::get('home_1_features_5_description', 'Track opens, clicks, bounces, and unsubscribes in real-time. Detailed reports with geographic and device insights.');
        $features6Title = (string) \App\Models\Setting::get('home_1_features_6_title', 'Built-in Billing');
        $features6Description = (string) \App\Models\Setting::get('home_1_features_6_description', 'Accept payments via Stripe, PayPal, or Paystack. Create plans, manage subscriptions, generate invoices automatically.');

        $aiBadge = (string) \App\Models\Setting::get('home_1_ai_badge', 'AI-Powered');
        $aiTitle = (string) \App\Models\Setting::get('home_1_ai_title', 'Write better emails with AI');
        $aiSubtitle = (string) \App\Models\Setting::get('home_1_ai_subtitle', 'Generate compelling subject lines, email copy, and calls-to-action in seconds.');
        $ai1Title = (string) \App\Models\Setting::get('home_1_ai_1_title', 'AI Content Generator');
        $ai1Description = (string) \App\Models\Setting::get('home_1_ai_1_description', 'Describe what you want to say and let AI craft the perfect email copy. Supports multiple tones and styles.');
        $ai2Title = (string) \App\Models\Setting::get('home_1_ai_2_title', 'Subject Line Optimizer');
        $ai2Description = (string) \App\Models\Setting::get('home_1_ai_2_description', 'Generate multiple subject line variations optimized for opens. A/B test with confidence.');

        $howTitle = (string) \App\Models\Setting::get('home_1_how_title', 'Get started in minutes');
        $howSubtitle = (string) \App\Models\Setting::get('home_1_how_subtitle', 'Deploy on your own server and start sending emails right away.');
        $how1Title = (string) \App\Models\Setting::get('home_1_how_1_title', 'Install & Configure');
        $how1Description = (string) \App\Models\Setting::get('home_1_how_1_description', 'Upload to your server, run the installer, and configure your settings. Works on any PHP 8.2+ hosting.');
        $how2Title = (string) \App\Models\Setting::get('home_1_how_2_title', 'Connect Email Providers');
        $how2Description = (string) \App\Models\Setting::get('home_1_how_2_description', 'Add your delivery servers — Amazon SES, Mailgun, SendGrid, or any SMTP. Configure sending domains.');
        $how3Title = (string) \App\Models\Setting::get('home_1_how_3_title', 'Start Sending');
        $how3Description = (string) \App\Models\Setting::get('home_1_how_3_description', 'Create lists, import subscribers, design campaigns, and start sending. Or invite customers to your SaaS.');
    } catch (\Throwable $e) {
        $featuresTitle = 'Everything you need to run email marketing at scale';
        $featuresSubtitle = "Whether you're sending for yourself or running a full SaaS business, MailZen has you covered.";
        $features1Title = 'Multi-Tenant SaaS Ready';
        $features1Description = 'Run your own email marketing SaaS. Manage customers, plans, billing, and permissions from a powerful admin panel.';
        $features2Title = 'Campaigns & Automation';
        $features2Description = 'Create one-time campaigns, recurring sends, or automated drip sequences. Drag-and-drop editor with responsive templates.';
        $features3Title = 'List Management';
        $features3Description = 'Unlimited lists with custom fields, tags, and segments. Import/export CSV, double opt-in, and GDPR compliance built-in.';
        $features4Title = 'Multiple Delivery Servers';
        $features4Description = 'Connect Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, or any SMTP. Load balance and rotate for maximum deliverability.';
        $features5Title = 'Real-Time Analytics';
        $features5Description = 'Track opens, clicks, bounces, and unsubscribes in real-time. Detailed reports with geographic and device insights.';
        $features6Title = 'Built-in Billing';
        $features6Description = 'Accept payments via Stripe, PayPal, or Paystack. Create plans, manage subscriptions, generate invoices automatically.';

        $aiBadge = 'AI-Powered';
        $aiTitle = 'Write better emails with AI';
        $aiSubtitle = 'Generate compelling subject lines, email copy, and calls-to-action in seconds.';
        $ai1Title = 'AI Content Generator';
        $ai1Description = 'Describe what you want to say and let AI craft the perfect email copy. Supports multiple tones and styles.';
        $ai2Title = 'Subject Line Optimizer';
        $ai2Description = 'Generate multiple subject line variations optimized for opens. A/B test with confidence.';

        $howTitle = 'Get started in minutes';
        $howSubtitle = 'Deploy on your own server and start sending emails right away.';
        $how1Title = 'Install & Configure';
        $how1Description = 'Upload to your server, run the installer, and configure your settings. Works on any PHP 8.2+ hosting.';
        $how2Title = 'Connect Email Providers';
        $how2Description = 'Add your delivery servers — Amazon SES, Mailgun, SendGrid, or any SMTP. Configure sending domains.';
        $how3Title = 'Start Sending';
        $how3Description = 'Create lists, import subscribers, design campaigns, and start sending. Or invite customers to your SaaS.';
    }

    try {
        $faqTitle = (string) \App\Models\Setting::get('home_faq_title', 'Frequently asked questions');
        $faqSubtitle = (string) \App\Models\Setting::get('home_faq_subtitle', 'Quick answers to common questions.');
        $faq1Question = (string) \App\Models\Setting::get('home_faq_1_question', 'What are the server requirements?');
        $faq1Answer = (string) \App\Models\Setting::get('home_faq_1_answer', 'PHP 8.2+, MySQL database, and a web server (Apache/Nginx). Redis is recommended for queues. Works on shared hosting, VPS, or dedicated servers.');
        $faq2Question = (string) \App\Models\Setting::get('home_faq_2_question', 'Can I run this as a SaaS for my clients?');
        $faq2Answer = (string) \App\Models\Setting::get('home_faq_2_answer', 'Absolutely! MailZen is built for multi-tenancy. Create customer accounts, define plans with limits, accept payments via Stripe/PayPal/Paystack, and let customers manage their own lists and campaigns.');
        $faq3Question = (string) \App\Models\Setting::get('home_faq_3_question', 'Which email providers are supported?');
        $faq3Answer = (string) \App\Models\Setting::get('home_faq_3_answer', 'Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, and any standard SMTP server. You can configure multiple providers and rotate between them for better deliverability.');
        $faq4Question = (string) \App\Models\Setting::get('home_faq_4_question', 'Is there a limit on subscribers or emails?');
        $faq4Answer = (string) \App\Models\Setting::get('home_faq_4_answer', 'No limits from our side. You can send as many emails as your server and email provider allow. The only limits are what you define in your customer plans.');
    } catch (\Throwable $e) {
        $faqTitle = 'Frequently asked questions';
        $faqSubtitle = 'Quick answers to common questions.';
        $faq1Question = 'What are the server requirements?';
        $faq1Answer = 'PHP 8.2+, MySQL database, and a web server (Apache/Nginx). Redis is recommended for queues. Works on shared hosting, VPS, or dedicated servers.';
        $faq2Question = 'Can I run this as a SaaS for my clients?';
        $faq2Answer = 'Absolutely! MailZen is built for multi-tenancy. Create customer accounts, define plans with limits, accept payments via Stripe/PayPal/Paystack, and let customers manage their own lists and campaigns.';
        $faq3Question = 'Which email providers are supported?';
        $faq3Answer = 'Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, and any standard SMTP server. You can configure multiple providers and rotate between them for better deliverability.';
        $faq4Question = 'Is there a limit on subscribers or emails?';
        $faq4Answer = 'No limits from our side. You can send as many emails as your server and email provider allow. The only limits are what you define in your customer plans.';
    }

    try {
        $pricingBadge = (string) \App\Models\Setting::get('home_pricing_badge', 'Our Pricing');
        $pricingTitle = (string) \App\Models\Setting::get('home_pricing_title', 'Choose Your Perfect Plan');
        $pricingSubtitle = (string) \App\Models\Setting::get('home_pricing_subtitle', 'Pick the MailZen plan that fits your email marketing goals');
        $pricingToggleMonthly = (string) \App\Models\Setting::get('home_pricing_toggle_monthly', 'Pay Monthly');
        $pricingToggleAnnual = (string) \App\Models\Setting::get('home_pricing_toggle_annual', 'Pay Annually');
        $pricingToggleSave = (string) \App\Models\Setting::get('home_pricing_toggle_save', '(save 20%)');
        $pricingPopularBadge = (string) \App\Models\Setting::get('home_pricing_popular_badge', 'Popular');
        $pricingCardCtaText = (string) \App\Models\Setting::get('home_pricing_card_cta_text', 'Get Started');
        $pricingCard1Title = (string) \App\Models\Setting::get('home_pricing_card_1_title', 'Starter');
        $pricingCard1Description = (string) \App\Models\Setting::get('home_pricing_card_1_description', 'For individuals, and early-stage startups');
        $pricingCard2Title = (string) \App\Models\Setting::get('home_pricing_card_2_title', 'Growth');
        $pricingCard2Description = (string) \App\Models\Setting::get('home_pricing_card_2_description', 'For individuals, and early-stage startups');
        $pricingCard3Title = (string) \App\Models\Setting::get('home_pricing_card_3_title', 'Scale');
        $pricingCard3Description = (string) \App\Models\Setting::get('home_pricing_card_3_description', 'For individuals, and early-stage startups');
        $pricingCard1CtaText = (string) \App\Models\Setting::get('home_pricing_card_1_cta_text', $pricingCardCtaText);
        $pricingCard2CtaText = (string) \App\Models\Setting::get('home_pricing_card_2_cta_text', $pricingCardCtaText);
        $pricingCard3CtaText = (string) \App\Models\Setting::get('home_pricing_card_3_cta_text', $pricingCardCtaText);

        $ctaTitle = (string) \App\Models\Setting::get('home_cta_title', 'Take control of your email marketing');
        $ctaSubtitle = (string) \App\Models\Setting::get('home_cta_subtitle', 'Stop paying monthly fees. Own your platform, own your data, and scale without limits.');
        $ctaPrimaryText = (string) \App\Models\Setting::get('home_cta_primary_text', 'Get Started Free');
        $ctaPrimaryUrl = (string) \App\Models\Setting::get('home_cta_primary_url', route('register'));
        $ctaSecondaryText = (string) \App\Models\Setting::get('home_cta_secondary_text', 'View on CodeCanyon');
        $ctaSecondaryUrl = (string) \App\Models\Setting::get('home_cta_secondary_url', 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414');
    } catch (\Throwable $e) {
        $pricingBadge = 'Our Pricing';
        $pricingTitle = 'Choose Your Perfect Plan';
        $pricingSubtitle = 'Pick the MailZen plan that fits your email marketing goals';
        $pricingToggleMonthly = 'Pay Monthly';
        $pricingToggleAnnual = 'Pay Annually';
        $pricingToggleSave = '(save 20%)';
        $pricingPopularBadge = 'Popular';
        $pricingCardCtaText = 'Get Started';
        $pricingCard1Title = 'Starter';
        $pricingCard1Description = 'For individuals, and early-stage startups';
        $pricingCard2Title = 'Growth';
        $pricingCard2Description = 'For individuals, and early-stage startups';
        $pricingCard3Title = 'Scale';
        $pricingCard3Description = 'For individuals, and early-stage startups';
        $pricingCard1CtaText = $pricingCardCtaText;
        $pricingCard2CtaText = $pricingCardCtaText;
        $pricingCard3CtaText = $pricingCardCtaText;

        $ctaTitle = 'Take control of your email marketing';
        $ctaSubtitle = 'Stop paying monthly fees. Own your platform, own your data, and scale without limits.';
        $ctaPrimaryText = 'Get Started Free';
        $ctaPrimaryUrl = route('register');
        $ctaSecondaryText = 'View on CodeCanyon';
        $ctaSecondaryUrl = 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414';
    }

    $ctaPrimaryUrl = is_string($ctaPrimaryUrl) && trim($ctaPrimaryUrl) !== '' ? $ctaPrimaryUrl : route('register');
    $ctaSecondaryUrl = is_string($ctaSecondaryUrl) && trim($ctaSecondaryUrl) !== '' ? $ctaSecondaryUrl : 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414';

    $pricingPlansMonthly = \App\Models\Plan::query()
        ->where('is_active', true)
        ->where('billing_cycle', 'monthly')
        ->orderBy('price')
        ->limit(3)
        ->get();

    $pricingPlansYearly = \App\Models\Plan::query()
        ->where('is_active', true)
        ->where('billing_cycle', 'yearly')
        ->orderBy('price')
        ->limit(3)
        ->get();

    $pricingAnnualDefault = $pricingPlansMonthly->count() === 0 && $pricingPlansYearly->count() > 0;

    try {
        $pricingSectionBadge = (string) \App\Models\Setting::get('pricing_section_badge', $pricingBadge);
        $pricingSectionTitle = (string) \App\Models\Setting::get('pricing_section_title', $pricingTitle);
        $pricingSectionSubtitle = (string) \App\Models\Setting::get('pricing_section_subtitle', $pricingSubtitle);
        $pricingSectionToggleMonthly = (string) \App\Models\Setting::get('pricing_section_toggle_monthly', $pricingToggleMonthly);
        $pricingSectionToggleAnnual = (string) \App\Models\Setting::get('pricing_section_toggle_annual', $pricingToggleAnnual);
        $pricingSectionToggleSave = (string) \App\Models\Setting::get('pricing_section_toggle_save', $pricingToggleSave);
        $pricingSectionPopularBadge = (string) \App\Models\Setting::get('pricing_section_popular_badge', $pricingPopularBadge);
    } catch (\Throwable $e) {
        $pricingSectionBadge = $pricingBadge;
        $pricingSectionTitle = $pricingTitle;
        $pricingSectionSubtitle = $pricingSubtitle;
        $pricingSectionToggleMonthly = $pricingToggleMonthly;
        $pricingSectionToggleAnnual = $pricingToggleAnnual;
        $pricingSectionToggleSave = $pricingToggleSave;
        $pricingSectionPopularBadge = $pricingPopularBadge;
    }
@endphp

<!-- Hero Section -->
<section class="relative min-h-[90vh] flex items-center overflow-hidden bg-white dark:bg-gray-950">
    <!-- Subtle Grid -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#f1f5f9_1px,transparent_1px),linear-gradient(to_bottom,#f1f5f9_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#1e293b_1px,transparent_1px),linear-gradient(to_bottom,#1e293b_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
    
    <!-- Gradient Orbs - Animated -->
    <div class="hero-orb-1 absolute top-20 left-1/4 w-96 h-96 bg-primary-500/20 rounded-full blur-3xl"></div>
    <div class="hero-orb-2 absolute bottom-20 right-1/4 w-96 h-96 bg-violet-500/20 rounded-full blur-3xl"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div class="max-w-4xl mx-auto text-center">
            <!-- Badge -->
            <div class="hero-badge gsap-fade-up inline-flex items-center gap-2 rounded-full border border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-950/50 px-4 py-2 text-sm text-primary-700 dark:text-primary-300 mb-8">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
                <span class="font-medium">Self-Hosted</span>
                <span class="text-primary-400 dark:text-primary-600">·</span>
                <span>Your Data, Your Control</span>
            </div>
            
            <!-- Headline -->
            <h1 class="hero-headline gsap-fade-up text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-gray-900 dark:text-white leading-[1.1]">
                The email marketing platform
                <span class="block mt-2 bg-gradient-to-r from-primary-600 via-violet-600 to-primary-600 bg-clip-text text-transparent">you actually own</span>
            </h1>
            
            <!-- Subheadline -->
            <p class="hero-subheadline gsap-fade-up mt-8 text-lg sm:text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                {{ $heroDescription }}
            </p>
            
            <!-- CTAs -->
            <div class="hero-ctas gsap-fade-up mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ $heroButtonUrl }}" class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-gray-900 dark:bg-white px-8 py-4 text-base font-semibold text-white dark:text-gray-900 shadow-xl shadow-gray-900/10 hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">
                    {{ $heroButtonText }}
                    @if($heroButtonType === 'video')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    @else
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    @endif
                </a>
                <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-8 py-4 text-base font-semibold text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                    Explore Features
                </a>
            </div>

            @if(is_string($heroScrollText) && trim($heroScrollText) !== '')
                <p class="hero-subheadline gsap-fade-up mt-4 text-sm text-gray-500 dark:text-gray-400">
                    {{ $heroScrollText }}
                </p>
            @endif
            
            <!-- Trust Indicators -->
            <div class="hero-trust gsap-fade-up mt-12 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-sm text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>100% Self-Hosted</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>White-Label Ready</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>One-Time Purchase</span>
                </div>
            </div>

            @if(!empty($heroImageUrl))
                <div class="mt-12">
                    <img src="{{ $heroImageUrl }}" alt="" class="mx-auto max-w-full rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl">
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Logos Section -->
<section class="logos-section py-16 border-y border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($logoUrls) && count($logoUrls) > 0)
            <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6">
                @foreach($logoUrls as $url)
                    <img src="{{ $url }}" alt="" class="h-10 w-auto opacity-80 hover:opacity-100 transition-opacity">
                @endforeach
            </div>
        @else
            <p class="logos-title gsap-fade-up text-center text-sm font-medium text-gray-500 dark:text-gray-400 mb-10">Integrates with your favorite email providers</p>
            <div class="logos-container flex flex-wrap items-center justify-center gap-x-16 gap-y-8">
                <!-- Amazon SES -->
                <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-400 dark:text-gray-500">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 1.5c-5.798 0-10.5 4.702-10.5 10.5s4.702 10.5 10.5 10.5 10.5-4.702 10.5-10.5-4.702-10.5-10.5-10.5zm0 19.5c-4.963 0-9-4.037-9-9s4.037-9 9-9 9 4.037 9 9-4.037 9-9 9z"/><path d="M8.25 15.75l3.75-3 3.75 3V8.25l-3.75 3-3.75-3z"/></svg>
                    <span class="font-semibold text-lg">Amazon SES</span>
                </div>
                <!-- Mailgun -->
                <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-400 dark:text-gray-500">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                    <span class="font-semibold text-lg">Mailgun</span>
                </div>
                <!-- SendGrid -->
                <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-400 dark:text-gray-500">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zm10-10h8v8h-8V3zm0 10h8v8h-8v-8z"/></svg>
                    <span class="font-semibold text-lg">SendGrid</span>
                </div>
                <!-- Postmark -->
                <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-400 dark:text-gray-500">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    <span class="font-semibold text-lg">Postmark</span>
                </div>
                <!-- SMTP -->
                <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-400 dark:text-gray-500">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path fill="none" stroke="currentColor" stroke-width="1.5" d="M22 6l-10 7L2 6"/></svg>
                    <span class="font-semibold text-lg">Any SMTP</span>
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Value Props -->
<section id="features" class="features-section py-24 lg:py-32 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <h2 class="section-title gsap-fade-up text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                {{ $featuresTitle }}
            </h2>
            <p class="section-subtitle gsap-fade-up mt-4 text-lg text-gray-600 dark:text-gray-400">
                {{ $featuresSubtitle }}
            </p>
        </div>

        <div class="features-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-primary-200 dark:hover:border-primary-800 hover:shadow-xl hover:shadow-primary-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $features1Title }}</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $features1Description }}</p>
            </div>

            <!-- Feature 2 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-violet-200 dark:hover:border-violet-800 hover:shadow-xl hover:shadow-violet-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $features2Title }}</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $features2Description }}</p>
            </div>

            <!-- Feature 3 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-emerald-200 dark:hover:border-emerald-800 hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $features3Title }}</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $features3Description }}</p>
            </div>

            <!-- Feature 4 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-amber-200 dark:hover:border-amber-800 hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $features4Title }}</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $features4Description }}</p>
            </div>

            <!-- Feature 5 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-rose-200 dark:hover:border-rose-800 hover:shadow-xl hover:shadow-rose-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $features5Title }}</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $features5Description }}</p>
            </div>

            <!-- Feature 6 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-indigo-200 dark:hover:border-indigo-800 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $features6Title }}</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $features6Description }}</p>
            </div>
        </div>
    </div>
</section>

<!-- AI Section -->
<section class="py-24 lg:py-32 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#e2e8f0_1px,transparent_1px),linear-gradient(to_bottom,#e2e8f0_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#1e293b_1px,transparent_1px),linear-gradient(to_bottom,#1e293b_1px,transparent_1px)] bg-[size:3rem_3rem] opacity-40"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-gradient-to-b from-violet-500/15 to-transparent blur-3xl"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 rounded-full border border-violet-200 dark:border-violet-800 bg-violet-50 dark:bg-violet-950/50 px-4 py-2 text-sm text-violet-700 dark:text-violet-300 mb-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                <span class="font-medium">{{ $aiBadge }}</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                {{ $aiTitle }}
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                {{ $aiSubtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <!-- AI Feature 1 -->
            <div class="relative p-8 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $ai1Title }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">{{ $ai1Description }}</p>
                    </div>
                </div>
            </div>

            <!-- AI Feature 2 -->
            <div class="relative p-8 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $ai2Title }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">{{ $ai2Description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-24 lg:py-32 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                {{ $howTitle }}
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                {{ $howSubtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Step 1 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">1</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $how1Title }}</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $how1Description }}</p>
            </div>

            <!-- Step 2 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-violet-600 dark:text-violet-400">2</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $how2Title }}</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $how2Description }}</p>
            </div>

            <!-- Step 3 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">3</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $how3Title }}</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $how3Description }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonial / Quote -->
<section class="relative py-24 lg:py-32 bg-gray-900 overflow-hidden">
    <!-- Light Arc Background -->
    <div class="absolute inset-0">
        <svg class="absolute bottom-0 left-0 w-full h-auto" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <path fill="rgba(255,255,255,0.03)" d="M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,138.7C672,128,768,160,864,181.3C960,203,1056,213,1152,197.3C1248,181,1344,139,1392,117.3L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
        <svg class="absolute top-0 left-0 w-full h-auto rotate-180" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <path fill="rgba(255,255,255,0.02)" d="M0,64L48,80C96,96,192,128,288,128C384,128,480,96,576,90.7C672,85,768,107,864,128C960,149,1056,171,1152,165.3C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
    <!-- Glow Effect -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-primary-500/10 rounded-full blur-3xl"></div>
    
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <svg class="w-12 h-12 text-gray-600 mx-auto mb-8" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
        <blockquote class="text-2xl sm:text-3xl font-medium text-white leading-relaxed">
            "Finally, an email marketing platform I can host myself. No more monthly fees eating into margins, no more data privacy concerns. MailZen just works."
        </blockquote>
        <div class="mt-8">
            <div class="font-semibold text-white">Marketing Agency Owner</div>
            <div class="text-sm text-gray-400">Managing 50+ client accounts</div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="pricing-section py-24 lg:py-32 bg-gray-50 dark:bg-gray-900" x-data="{ annual: @js($pricingAnnualDefault) }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 rounded-full border border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-950/50 px-4 py-2 text-sm text-primary-700 dark:text-primary-300 mb-6">
                <span class="font-medium">{{ $pricingSectionBadge }}</span>
            </div>
            <h2 class="pricing-title gsap-fade-up text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white italic">
                {{ $pricingSectionTitle }}
            </h2>
            <p class="pricing-subtitle gsap-fade-up mt-4 text-lg text-gray-600 dark:text-gray-400">
                {{ $pricingSectionSubtitle }}
            </p>
        </div>

        <!-- Billing Toggle -->
        <div class="flex items-center justify-center gap-4 mb-12">
            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $pricingSectionToggleMonthly }}</span>
            <button @click="annual = !annual" class="relative w-14 h-7 bg-primary-600 rounded-full transition-colors">
                <span class="absolute top-1 left-1 w-5 h-5 bg-white rounded-full transition-transform" :class="{ 'translate-x-7': annual }"></span>
            </button>
            <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ $pricingSectionToggleAnnual }} <span class="text-primary-500">{{ $pricingSectionToggleSave }}</span></span>
        </div>

        <!-- Pricing Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto" x-show="!annual">
            @foreach($pricingPlansMonthly as $idx => $plan)
                @php
                    $isFeatured = ($plan->is_popular ?? false) === true;
                    if (!$isFeatured && $pricingPlansMonthly->where('is_popular', true)->count() === 0) {
                        $isFeatured = $idx === 1;
                    }
                    $cycle = $plan->billing_cycle === 'yearly' ? 'year' : 'month';
                    $ctaText = is_string($plan->cta_text ?? null) && trim((string) $plan->cta_text) !== '' ? (string) $plan->cta_text : 'Get Started';
                    $planFeatures = $plan->features ?? null;
                    $pros = [];
                    $cons = [];
                    if (is_array($planFeatures) && (array_key_exists('pros', $planFeatures) || array_key_exists('cons', $planFeatures))) {
                        $pros = is_array($planFeatures['pros'] ?? null) ? $planFeatures['pros'] : [];
                        $cons = is_array($planFeatures['cons'] ?? null) ? $planFeatures['cons'] : [];
                    } elseif (is_array($planFeatures)) {
                        $pros = $planFeatures;
                    }
                @endphp
                <div class="pricing-card gsap-fade-up relative bg-white dark:bg-gray-950 rounded-3xl p-8 {{ $isFeatured ? 'border-2 border-primary-500 shadow-xl shadow-primary-500/10' : 'border border-gray-200 dark:border-gray-800 shadow-sm' }}">
                    @if($isFeatured)
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-1.5 bg-primary-600 text-white text-sm font-semibold rounded-full">
                            {{ $pricingSectionPopularBadge }}
                        </div>
                    @endif

                    <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mb-6">
                        @if($isFeatured)
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @elseif($idx === 0)
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @else
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        @endif
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $plan->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ $plan->description }}</p>

                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white"><span class="text-sm font-semibold align-top">{{ $plan->currency }}</span> {{ number_format((float) $plan->price, 2) }}</span>
                        <span class="text-gray-500 dark:text-gray-400">/{{ $cycle }}</span>
                    </div>

                    <a href="{{ route('pricing') }}" class="flex items-center justify-center gap-2 w-full py-3 px-6 rounded-xl {{ $isFeatured ? 'bg-primary-600 text-white hover:bg-primary-700' : 'border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }} transition-colors mb-8">
                        {{ $ctaText }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>

                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Features</h4>
                        <ul class="space-y-3">
                            @foreach($pros as $feature)
                                @if(is_string($feature) && trim($feature) !== '')
                                    <li class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endif
                            @endforeach

                            @foreach($cons as $feature)
                                @if(is_string($feature) && trim($feature) !== '')
                                    <li class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto" x-show="annual">
            @foreach($pricingPlansYearly as $idx => $plan)
                @php
                    $isFeatured = ($plan->is_popular ?? false) === true;
                    if (!$isFeatured && $pricingPlansYearly->where('is_popular', true)->count() === 0) {
                        $isFeatured = $idx === 1;
                    }
                    $cycle = $plan->billing_cycle === 'yearly' ? 'year' : 'month';
                    $ctaText = is_string($plan->cta_text ?? null) && trim((string) $plan->cta_text) !== '' ? (string) $plan->cta_text : 'Get Started';
                    $planFeatures = $plan->features ?? null;
                    $pros = [];
                    $cons = [];
                    if (is_array($planFeatures) && (array_key_exists('pros', $planFeatures) || array_key_exists('cons', $planFeatures))) {
                        $pros = is_array($planFeatures['pros'] ?? null) ? $planFeatures['pros'] : [];
                        $cons = is_array($planFeatures['cons'] ?? null) ? $planFeatures['cons'] : [];
                    } elseif (is_array($planFeatures)) {
                        $pros = $planFeatures;
                    }
                @endphp
                <div class="pricing-card gsap-fade-up relative bg-white dark:bg-gray-950 rounded-3xl p-8 {{ $isFeatured ? 'border-2 border-primary-500 shadow-xl shadow-primary-500/10' : 'border border-gray-200 dark:border-gray-800 shadow-sm' }}">
                    @if($isFeatured)
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-1.5 bg-primary-600 text-white text-sm font-semibold rounded-full">
                            {{ $pricingSectionPopularBadge }}
                        </div>
                    @endif

                    <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mb-6">
                        @if($isFeatured)
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @elseif($idx === 0)
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @else
                            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        @endif
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $plan->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ $plan->description }}</p>

                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white"><span class="text-sm font-semibold align-top">{{ $plan->currency }}</span> {{ number_format((float) $plan->price, 2) }}</span>
                        <span class="text-gray-500 dark:text-gray-400">/{{ $cycle }}</span>
                    </div>

                    <a href="{{ route('pricing') }}" class="flex items-center justify-center gap-2 w-full py-3 px-6 rounded-xl {{ $isFeatured ? 'bg-primary-600 text-white hover:bg-primary-700' : 'border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }} transition-colors mb-8">
                        {{ $ctaText }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>

                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Features</h4>
                        <ul class="space-y-3">
                            @foreach($pros as $feature)
                                @if(is_string($feature) && trim($feature) !== '')
                                    <li class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endif
                            @endforeach

                            @foreach($cons as $feature)
                                @if(is_string($feature) && trim($feature) !== '')
                                    <li class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-24 lg:py-32 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                {{ $faqTitle }}
            </h2>
            @if(is_string($faqSubtitle) && trim($faqSubtitle) !== '')
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">{{ $faqSubtitle }}</p>
            @endif
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 overflow-hidden">
                <button @click="open = open === 1 ? null : 1" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $faq1Question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-6 pb-5 text-gray-600 dark:text-gray-400">
                    {{ $faq1Answer }}
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 overflow-hidden">
                <button @click="open = open === 2 ? null : 2" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $faq2Question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-6 pb-5 text-gray-600 dark:text-gray-400">
                    {{ $faq2Answer }}
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 overflow-hidden">
                <button @click="open = open === 3 ? null : 3" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $faq3Question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-6 pb-5 text-gray-600 dark:text-gray-400">
                    {{ $faq3Answer }}
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 overflow-hidden">
                <button @click="open = open === 4 ? null : 4" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $faq4Question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-6 pb-5 text-gray-600 dark:text-gray-400">
                    {{ $faq4Answer }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="py-24 lg:py-32 bg-gray-900 dark:bg-black relative overflow-hidden">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#374151_1px,transparent_1px),linear-gradient(to_bottom,#374151_1px,transparent_1px)] bg-[size:4rem_4rem] opacity-20"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-primary-500/20 to-transparent blur-3xl"></div>
    
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight">
            {{ $ctaTitle }}
        </h2>
        <p class="mt-6 text-xl text-gray-400 max-w-2xl mx-auto">
            {{ $ctaSubtitle }}
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ $ctaPrimaryUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-white px-8 py-4 text-base font-semibold text-gray-900 hover:bg-gray-100 transition-colors">
                {{ $ctaPrimaryText }}
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ $ctaSecondaryUrl }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border border-gray-700 px-8 py-4 text-base font-semibold text-white hover:bg-gray-800 transition-colors">
                {{ $ctaSecondaryText }}
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function() {
    const shouldRun = () => {
        return document.body && document.body.dataset && document.body.dataset.mailpursePage === 'home-1';
    };

    const teardown = () => {
        if (!shouldRun()) {
            return;
        }
        if (window.__mailpurseHomeGsapCtx && typeof window.__mailpurseHomeGsapCtx.revert === 'function') {
            try {
                window.__mailpurseHomeGsapCtx.revert();
            } catch (e) {
            }
        }
        window.__mailpurseHomeGsapCtx = null;

        if (typeof ScrollTrigger !== 'undefined' && ScrollTrigger && typeof ScrollTrigger.getAll === 'function') {
            ScrollTrigger.getAll().forEach((t) => {
                try {
                    t.kill();
                } catch (e) {
                }
            });
        }
    };

    const init = () => {
        if (!shouldRun()) {
            return;
        }
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
            return;
        }

        teardown();

        if (typeof ScrollToPlugin !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);
        } else {
            gsap.registerPlugin(ScrollTrigger);
        }

        window.__mailpurseHomeGsapCtx = gsap.context(() => {
            // Hero Section Animations
            const heroTl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            
            heroTl
                .to('.hero-badge', { opacity: 1, y: 0, duration: 0.8 })
                .to('.hero-headline', { opacity: 1, y: 0, duration: 0.8 }, '-=0.5')
                .to('.hero-subheadline', { opacity: 1, y: 0, duration: 0.8 }, '-=0.5')
                .to('.hero-ctas', { opacity: 1, y: 0, duration: 0.8 }, '-=0.5')
                .to('.hero-trust', { opacity: 1, y: 0, duration: 0.8 }, '-=0.5');
            
            // Floating orbs animation
            gsap.to('.hero-orb-1', {
                x: 30,
                y: -20,
                duration: 4,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut'
            });
            
            gsap.to('.hero-orb-2', {
                x: -30,
                y: 20,
                duration: 5,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut'
            });
            
            // Logos Section - Staggered fade in
            gsap.to('.logos-title', {
                scrollTrigger: {
                    trigger: '.logos-section',
                    start: 'top 80%'
                },
                opacity: 1,
                y: 0,
                duration: 0.6
            });
            
            gsap.to('.logo-item', {
                scrollTrigger: {
                    trigger: '.logos-container',
                    start: 'top 80%'
                },
                opacity: 1,
                y: 0,
                duration: 0.6,
                stagger: 0.1
            });
            
            // Features Section
            gsap.to('.section-title', {
                scrollTrigger: {
                    trigger: '.features-section',
                    start: 'top 80%'
                },
                opacity: 1,
                y: 0,
                duration: 0.6
            });
            
            gsap.to('.section-subtitle', {
                scrollTrigger: {
                    trigger: '.features-section',
                    start: 'top 80%'
                },
                opacity: 1,
                y: 0,
                duration: 0.6,
                delay: 0.2
            });
            
            gsap.to('.feature-card', {
                scrollTrigger: {
                    trigger: '.features-grid',
                    start: 'top 80%'
                },
                opacity: 1,
                y: 0,
                duration: 0.6,
                stagger: 0.1
            });
            
            // Generic scroll animations for remaining sections
            const sections = document.querySelectorAll('section:not(:first-child)');
            sections.forEach(section => {
                const fadeElements = section.querySelectorAll('.gsap-fade-up:not(.logo-item):not(.feature-card):not(.section-title):not(.section-subtitle):not(.logos-title)');
                fadeElements.forEach(el => {
                    gsap.to(el, {
                        scrollTrigger: {
                            trigger: el,
                            start: 'top 85%'
                        },
                        opacity: 1,
                        y: 0,
                        duration: 0.6
                    });
                });
                
                const scaleElements = section.querySelectorAll('.gsap-scale-in');
                scaleElements.forEach(el => {
                    gsap.to(el, {
                        scrollTrigger: {
                            trigger: el,
                            start: 'top 85%'
                        },
                        opacity: 1,
                        scale: 1,
                        duration: 0.8,
                        ease: 'back.out(1.2)'
                    });
                });
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                if (anchor.dataset && anchor.dataset.gsapScrollBound === '1') {
                    return;
                }
                if (anchor.dataset) {
                    anchor.dataset.gsapScrollBound = '1';
                }
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        gsap.to(window, {
                            duration: 1,
                            scrollTo: { y: target, offsetY: 80 },
                            ease: 'power2.inOut'
                        });
                    }
                });
            });
        });

        try {
            ScrollTrigger.refresh(true);
        } catch (e) {
        }
    };

    if (window.Turbo) {
        document.addEventListener('turbo:before-cache', teardown);
        document.addEventListener('turbo:load', init);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush
