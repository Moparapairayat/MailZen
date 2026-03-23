@extends('layouts.public')

@section('title', 'MailZen - Self-Hosted Email Marketing & Automation Platform | White-Label SaaS')
@section('meta_description', 'Build your own email marketing SaaS with MailZen. Self-hosted, white-label, unlimited subscribers. One-time purchase, no monthly fees. Supports Amazon SES, Mailgun, SendGrid.')
@section('meta_keywords', 'email marketing software, self-hosted email platform, white-label email marketing, email automation, newsletter software, bulk email sender, email SaaS')
@section('pageId', 'home-3')

@section('content')
@php
    try {
        $heroDescription = (string) \App\Models\Setting::get('home_3_hero_description', 'Self-hosted email automation software with white-label branding, multi-tenant support, and built-in billing. One-time purchase, no monthly fees.');
        $heroScrollText = (string) \App\Models\Setting::get('home_3_hero_scroll_text', '');
        $heroButtonText = (string) \App\Models\Setting::get('home_3_hero_button_text', 'Get Started — $29');
        $heroButtonType = (string) \App\Models\Setting::get('home_3_hero_button_type', 'link');
        $heroButtonUrl = (string) \App\Models\Setting::get('home_3_hero_button_url', route('register'));
        $heroImagePath = (string) \App\Models\Setting::get('home_3_hero_image', '');
    } catch (\Throwable $e) {
        $heroDescription = 'Self-hosted email automation software with white-label branding, multi-tenant support, and built-in billing. One-time purchase, no monthly fees.';
        $heroScrollText = '';
        $heroButtonText = 'Get Started — $29';
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
        $logoPaths = \App\Models\Setting::get('home_3_logos', []);
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
        $faqTitle = (string) \App\Models\Setting::get('home_faq_title', 'Frequently Asked Questions');
        $faqSubtitle = (string) \App\Models\Setting::get('home_faq_subtitle', 'Everything you need to know about MailZen');
        $faq1Question = (string) \App\Models\Setting::get('home_faq_1_question', 'What are the server requirements?');
        $faq1Answer = (string) \App\Models\Setting::get('home_faq_1_answer', 'MailZen requires PHP 8.2+, MySQL 5.7+ or MariaDB 10.3+, and a web server (Apache or Nginx). Redis is recommended for queue processing. Works on shared hosting, VPS, or dedicated servers.');
        $faq2Question = (string) \App\Models\Setting::get('home_faq_2_question', 'Can I run this as a SaaS for my clients?');
        $faq2Answer = (string) \App\Models\Setting::get('home_faq_2_answer', 'Yes! MailZen is built for multi-tenancy. Create customer accounts, define subscription plans with limits, accept payments via Stripe/PayPal/Paystack, and let customers manage their own lists and campaigns.');
        $faq3Question = (string) \App\Models\Setting::get('home_faq_3_question', 'Which email providers are supported?');
        $faq3Answer = (string) \App\Models\Setting::get('home_faq_3_answer', 'Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, ZeptoMail, and any standard SMTP server. You can configure multiple providers and rotate between them for better deliverability.');
        $faq4Question = (string) \App\Models\Setting::get('home_faq_4_question', 'Is there a limit on subscribers or emails?');
        $faq4Answer = (string) \App\Models\Setting::get('home_faq_4_answer', 'No limits from MailZen. You can have unlimited subscribers and send unlimited emails. The only limits are what your server and email provider allow.');
        $faq5Question = (string) \App\Models\Setting::get('home_faq_5_question', 'Do I get updates and support?');
        $faq5Answer = (string) \App\Models\Setting::get('home_faq_5_answer', 'Yes! Your purchase includes 6 months of free updates and support. After that, you can optionally renew for continued updates, or keep using your current version forever.');
    } catch (\Throwable $e) {
        $faqTitle = 'Frequently Asked Questions';
        $faqSubtitle = 'Everything you need to know about MailZen';
        $faq1Question = 'What are the server requirements?';
        $faq1Answer = 'MailZen requires PHP 8.2+, MySQL 5.7+ or MariaDB 10.3+, and a web server (Apache or Nginx). Redis is recommended for queue processing. Works on shared hosting, VPS, or dedicated servers.';
        $faq2Question = 'Can I run this as a SaaS for my clients?';
        $faq2Answer = 'Yes! MailZen is built for multi-tenancy. Create customer accounts, define subscription plans with limits, accept payments via Stripe/PayPal/Paystack, and let customers manage their own lists and campaigns.';
        $faq3Question = 'Which email providers are supported?';
        $faq3Answer = 'Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, ZeptoMail, and any standard SMTP server. You can configure multiple providers and rotate between them for better deliverability.';
        $faq4Question = 'Is there a limit on subscribers or emails?';
        $faq4Answer = 'No limits from MailZen. You can have unlimited subscribers and send unlimited emails. The only limits are what your server and email provider allow.';
        $faq5Question = 'Do I get updates and support?';
        $faq5Answer = 'Yes! Your purchase includes 6 months of free updates and support. After that, you can optionally renew for continued updates, or keep using your current version forever.';
    }

    try {
        $pricingBadge = (string) \App\Models\Setting::get('home_pricing_badge', 'Limited Time Offer');
        $pricingTitle = (string) \App\Models\Setting::get('home_pricing_title', 'Simple, Transparent Pricing');
        $pricingSubtitle = (string) \App\Models\Setting::get('home_pricing_subtitle', 'One-time payment. No subscriptions. No hidden fees.');
        $pricingPopularBadge = (string) \App\Models\Setting::get('home_pricing_popular_badge', 'Best Value');
        $pricingCard2Title = (string) \App\Models\Setting::get('home_pricing_card_2_title', 'MailZen License');
        $pricingCard2CtaText = (string) \App\Models\Setting::get('home_pricing_card_2_cta_text', 'Get MailZen Now');

        $ctaTitle = (string) \App\Models\Setting::get('home_cta_title', 'Ready to Own Your Email Marketing Platform?');
        $ctaSubtitle = (string) \App\Models\Setting::get('home_cta_subtitle', 'Join 500+ businesses who switched to MailZen and saved thousands on email marketing costs.');
        $ctaPrimaryText = (string) \App\Models\Setting::get('home_cta_primary_text', 'Get Started for $29');
        $ctaPrimaryUrl = (string) \App\Models\Setting::get('home_cta_primary_url', route('register'));
        $ctaSecondaryText = (string) \App\Models\Setting::get('home_cta_secondary_text', 'View on CodeCanyon');
        $ctaSecondaryUrl = (string) \App\Models\Setting::get('home_cta_secondary_url', 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414');
        $ctaNote = (string) \App\Models\Setting::get('home_cta_note', 'One-time payment • Lifetime access • 30-day money-back guarantee');
    } catch (\Throwable $e) {
        $pricingBadge = 'Limited Time Offer';
        $pricingTitle = 'Simple, Transparent Pricing';
        $pricingSubtitle = 'One-time payment. No subscriptions. No hidden fees.';
        $pricingPopularBadge = 'Best Value';
        $pricingCard2Title = 'MailZen License';
        $pricingCard2CtaText = 'Get MailZen Now';

        $ctaTitle = 'Ready to Own Your Email Marketing Platform?';
        $ctaSubtitle = 'Join 500+ businesses who switched to MailZen and saved thousands on email marketing costs.';
        $ctaPrimaryText = 'Get Started for $29';
        $ctaPrimaryUrl = route('register');
        $ctaSecondaryText = 'View on CodeCanyon';
        $ctaSecondaryUrl = 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414';
        $ctaNote = 'One-time payment • Lifetime access • 30-day money-back guarantee';
    }

    $ctaPrimaryUrl = is_string($ctaPrimaryUrl) && trim($ctaPrimaryUrl) !== '' ? $ctaPrimaryUrl : route('register');
    $ctaSecondaryUrl = is_string($ctaSecondaryUrl) && trim($ctaSecondaryUrl) !== '' ? $ctaSecondaryUrl : 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414';
@endphp

<!-- Hero Section - Conversion Focused -->
<section class="relative min-h-screen flex items-center bg-white overflow-hidden">
    <!-- Subtle Background Pattern -->
    <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] bg-[size:20px_20px] opacity-50"></div>
    <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-primary-50/50 to-transparent"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <!-- Left - Sales Copy -->
            <div>
                <!-- Social Proof Badge -->
                <div class="inline-flex items-center gap-3 rounded-full bg-amber-50 border border-amber-200 px-4 py-2 mb-8">
                    <div class="flex -space-x-2">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 border-2 border-white"></div>
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-green-400 to-green-600 border-2 border-white"></div>
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 border-2 border-white"></div>
                    </div>
                    <span class="text-sm font-medium text-amber-800">Trusted by 500+ businesses worldwide</span>
                </div>
                
                <!-- Main Headline - SEO Optimized -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-[1.1] tracking-tight">
                    Build Your Own
                    <span class="relative inline-block">
                        <span class="relative z-10 text-primary-600">Email Marketing</span>
                        <span class="absolute bottom-2 left-0 w-full h-3 bg-primary-200/60 -z-0"></span>
                    </span>
                    <span class="block">SaaS Platform</span>
                </h1>
                
                <!-- Value Proposition -->
                <p class="mt-6 text-xl text-gray-600 leading-relaxed max-w-xl">
                    {{ $heroDescription }}
                </p>
                
                <!-- Key Benefits -->
                <div class="mt-8 grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Unlimited Subscribers</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">White-Label Ready</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Multi-Tenant SaaS</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Lifetime Updates</span>
                    </div>
                </div>
                
                <!-- CTA Section -->
                <div class="mt-10 flex flex-col sm:flex-row gap-4">
                    <a href="{{ $heroButtonUrl }}" class="group inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-primary-600/25 hover:bg-primary-700 hover:shadow-primary-600/40 transition-all">
                        {{ $heroButtonText }}
                        @if($heroButtonType === 'video')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        @else
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        @endif
                    </a>
                    <a href="#demo" class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white px-8 py-4 text-lg font-semibold text-gray-700 hover:border-gray-300 hover:bg-gray-50 transition-all">
                        <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Watch Demo
                    </a>
                </div>

                @if(is_string($heroScrollText) && trim($heroScrollText) !== '')
                    <p class="mt-4 text-sm text-gray-500">{{ $heroScrollText }}</p>
                @endif
                
                <!-- Trust Signals -->
                <div class="mt-8 flex items-center gap-6 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Secure & Private</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>5-Min Setup</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>One-Time Payment</span>
                    </div>
                </div>
            </div>
            
            <!-- Right - Product Screenshot -->
            <div class="relative">
                @if(!empty($heroImageUrl))
                    <img src="{{ $heroImageUrl }}" alt="" class="relative max-w-full rounded-2xl border border-gray-200 shadow-2xl">
                @else
                    <div class="absolute -inset-4 bg-gradient-to-r from-primary-500/20 via-purple-500/20 to-pink-500/20 rounded-3xl blur-2xl"></div>
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-gray-200 bg-white">
                        <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 border-b border-gray-200">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="bg-white rounded-lg px-4 py-1.5 text-sm text-gray-400 border border-gray-200">app.mailzen.com/dashboard</div>
                            </div>
                        </div>
                        <div class="p-6 bg-gray-50">
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="bg-white rounded-xl p-4 border border-gray-100">
                                    <div class="text-2xl font-bold text-gray-900">12,847</div>
                                    <div class="text-sm text-gray-500">Subscribers</div>
                                </div>
                                <div class="bg-white rounded-xl p-4 border border-gray-100">
                                    <div class="text-2xl font-bold text-primary-600">68.4%</div>
                                    <div class="text-sm text-gray-500">Open Rate</div>
                                </div>
                                <div class="bg-white rounded-xl p-4 border border-gray-100">
                                    <div class="text-2xl font-bold text-green-600">$4,280</div>
                                    <div class="text-sm text-gray-500">Revenue</div>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-gray-100">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="font-medium text-gray-900">Recent Campaigns</span>
                                    <span class="text-sm text-primary-600">View All</span>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                                        <span class="text-sm text-gray-700">Welcome Series</span>
                                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">Active</span>
                                    </div>
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-sm text-gray-700">Product Launch</span>
                                        <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700">Scheduled</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Logos/Integrations - SEO Keywords -->
<section class="py-12 bg-gray-50 border-y border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($logoUrls) && count($logoUrls) > 0)
            <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6">
                @foreach($logoUrls as $url)
                    <img src="{{ $url }}" alt="" class="h-10 w-auto opacity-80 hover:opacity-100 transition-opacity">
                @endforeach
            </div>
        @else
            <p class="text-center text-sm font-medium text-gray-500 mb-8">Seamlessly integrates with leading email delivery providers</p>
            <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6">
                <div class="flex items-center gap-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="font-semibold">Amazon SES</span>
                </div>
                <div class="flex items-center gap-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="font-semibold">Mailgun</span>
                </div>
                <div class="flex items-center gap-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="font-semibold">SendGrid</span>
                </div>
                <div class="flex items-center gap-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="font-semibold">Postmark</span>
                </div>
                <div class="flex items-center gap-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="font-semibold">SparkPost</span>
                </div>
                <div class="flex items-center gap-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="font-semibold">SMTP</span>
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Problem/Solution Section -->
<section class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                Stop Paying Monthly Fees for Email Marketing
            </h2>
            <p class="mt-4 text-xl text-gray-600">
                Mailchimp charges $100+/month for 10K subscribers. With MailZen, you pay once and own it forever.
            </p>
        </div>
        
        <!-- Comparison Cards -->
        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <!-- Before -->
            <div class="rounded-2xl border-2 border-red-100 bg-red-50/50 p-8">
                <div class="inline-flex items-center gap-2 text-red-600 font-semibold mb-6">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Traditional SaaS
                </div>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3 text-gray-700">
                        <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>$100-500/month recurring fees</span>
                    </li>
                    <li class="flex items-start gap-3 text-gray-700">
                        <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Your data on someone else's servers</span>
                    </li>
                    <li class="flex items-start gap-3 text-gray-700">
                        <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Limited customization options</span>
                    </li>
                    <li class="flex items-start gap-3 text-gray-700">
                        <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Their branding on your emails</span>
                    </li>
                </ul>
            </div>
            
            <!-- After -->
            <div class="rounded-2xl border-2 border-green-200 bg-green-50/50 p-8">
                <div class="inline-flex items-center gap-2 text-green-600 font-semibold mb-6">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    With MailZen
                </div>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3 text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>$29 one-time</strong> — own it forever</span>
                    </li>
                    <li class="flex items-start gap-3 text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>100% self-hosted, your data stays private</span>
                    </li>
                    <li class="flex items-start gap-3 text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Full source code, customize everything</span>
                    </li>
                    <li class="flex items-start gap-3 text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Complete white-label branding</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid - SEO Rich -->
<section id="features" class="py-20 lg:py-28 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                Everything You Need for Professional Email Marketing
            </h2>
            <p class="mt-4 text-xl text-gray-600 max-w-2xl mx-auto">
                From simple newsletters to complex automation workflows, MailZen has you covered.
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <article class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg hover:border-primary-100 transition-all">
                <div class="w-14 h-14 rounded-2xl bg-primary-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Email Campaigns</h3>
                <p class="text-gray-600">Create beautiful email campaigns with our drag-and-drop editor. Schedule, segment, and send to thousands instantly.</p>
            </article>
            
            <!-- Feature 2 -->
            <article class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg hover:border-primary-100 transition-all">
                <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Marketing Automation</h3>
                <p class="text-gray-600">Set up autoresponders, drip sequences, and triggered emails. Nurture leads on autopilot 24/7.</p>
            </article>
            
            <!-- Feature 3 -->
            <article class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg hover:border-primary-100 transition-all">
                <div class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Subscriber Management</h3>
                <p class="text-gray-600">Unlimited lists, custom fields, tags, and advanced segmentation. Import/export with CSV support.</p>
            </article>
            
            <!-- Feature 4 -->
            <article class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg hover:border-primary-100 transition-all">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Analytics & Reporting</h3>
                <p class="text-gray-600">Track opens, clicks, bounces, and conversions in real-time. Detailed reports with geographic insights.</p>
            </article>
            
            <!-- Feature 5 -->
            <article class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg hover:border-primary-100 transition-all">
                <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Multi-Tenant SaaS</h3>
                <p class="text-gray-600">Run your own email marketing SaaS. Manage customers, create plans, and accept payments automatically.</p>
            </article>
            
            <!-- Feature 6 -->
            <article class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg hover:border-primary-100 transition-all">
                <div class="w-14 h-14 rounded-2xl bg-pink-100 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">AI Writing Tools</h3>
                <p class="text-gray-600">Generate subject lines, email copy, and CTAs with built-in AI. Write better emails in seconds.</p>
            </article>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 rounded-full bg-primary-50 border border-primary-100 px-4 py-2 text-sm text-primary-700 mb-6">
                <span class="font-semibold">{{ $pricingBadge }}</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                {{ $pricingTitle }}
            </h2>
            <p class="mt-4 text-xl text-gray-600">
                {{ $pricingSubtitle }}
            </p>
        </div>
        
        <div class="max-w-lg mx-auto">
            <div class="relative rounded-3xl border-2 border-primary-500 bg-white p-8 shadow-xl shadow-primary-500/10">
                <!-- Popular Badge -->
                <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                    <div class="bg-primary-600 text-white px-6 py-2 rounded-full text-sm font-semibold shadow-lg">
                        {{ $pricingPopularBadge }}
                    </div>
                </div>
                
                <div class="text-center pt-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $pricingCard2Title }}</h3>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <span class="text-5xl font-bold text-gray-900">$29</span>
                        <div class="text-left">
                            <span class="block text-sm text-gray-500 line-through">$79</span>
                            <span class="block text-sm font-medium text-green-600">Save 63%</span>
                        </div>
                    </div>
                    <p class="mt-2 text-gray-500">One-time payment, lifetime access</p>
                </div>
                
                <div class="mt-8 space-y-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700">Unlimited subscribers & emails</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700">White-label branding</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700">Multi-tenant SaaS mode</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700">Stripe, PayPal, Paystack billing</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700">AI writing tools included</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700">6 months of free updates</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700">Full source code access</span>
                    </div>
                </div>
                
                <div class="mt-8">
                    <a href="{{ route('register') }}" class="block w-full text-center rounded-xl bg-primary-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-primary-600/25 hover:bg-primary-700 transition-all">
                        {{ $pricingCard2CtaText }}
                    </a>
                    <p class="mt-4 text-center text-sm text-gray-500">
                        30-day money-back guarantee
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-20 lg:py-28 bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">
                Loved by Businesses Worldwide
            </h2>
            <p class="mt-4 text-xl text-gray-400">
                See what our customers are saying about MailZen
            </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700">
                <div class="flex gap-1 mb-4">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="text-gray-300 mb-6">"Switched from Mailchimp and saved $1,200 in the first year. The white-label feature is perfect for my agency."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600"></div>
                    <div>
                        <div class="font-medium text-white">Sarah M.</div>
                        <div class="text-sm text-gray-500">Digital Agency Owner</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700">
                <div class="flex gap-1 mb-4">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="text-gray-300 mb-6">"Running my own email SaaS now with 50+ paying customers. MailZen paid for itself in the first week."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-green-600"></div>
                    <div>
                        <div class="font-medium text-white">James K.</div>
                        <div class="text-sm text-gray-500">SaaS Founder</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700">
                <div class="flex gap-1 mb-4">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="text-gray-300 mb-6">"Setup was incredibly easy. Connected Amazon SES and was sending emails within 30 minutes. Great documentation!"</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600"></div>
                    <div>
                        <div class="font-medium text-white">Michael R.</div>
                        <div class="text-sm text-gray-500">E-commerce Owner</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section - SEO Rich -->
<section id="faq" class="py-20 lg:py-28 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                {{ $faqTitle }}
            </h2>
            <p class="mt-4 text-xl text-gray-600">
                {{ $faqSubtitle }}
            </p>
        </div>
        
        <div class="space-y-4" x-data="{ open: 1 }">
            <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <button type="button" @click="open = open === 1 ? null : 1" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-semibold text-gray-900">{{ $faq1Question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-6 pb-5 text-gray-600">
                    {{ $faq1Answer }}
                </div>
            </div>
            
            <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <button type="button" @click="open = open === 2 ? null : 2" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-semibold text-gray-900">{{ $faq2Question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-6 pb-5 text-gray-600">
                    {{ $faq2Answer }}
                </div>
            </div>
            
            <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <button type="button" @click="open = open === 3 ? null : 3" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-semibold text-gray-900">{{ $faq3Question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-6 pb-5 text-gray-600">
                    {{ $faq3Answer }}
                </div>
            </div>
            
            <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <button type="button" @click="open = open === 4 ? null : 4" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-semibold text-gray-900">{{ $faq4Question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-6 pb-5 text-gray-600">
                    {{ $faq4Answer }}
                </div>
            </div>
            
            <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <button type="button" @click="open = open === 5 ? null : 5" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-semibold text-gray-900">{{ $faq5Question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 5 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 5" x-collapse class="px-6 pb-5 text-gray-600">
                    {{ $faq5Answer }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="py-20 lg:py-28 bg-primary-600 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.1),transparent_50%)]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_80%,rgba(255,255,255,0.08),transparent_40%)]"></div>
    
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight">
            {{ $ctaTitle }}
        </h2>
        <p class="mt-6 text-xl text-primary-100 max-w-2xl mx-auto">
            {{ $ctaSubtitle }}
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ $ctaPrimaryUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-white px-8 py-4 text-lg font-semibold text-primary-600 shadow-lg hover:bg-gray-50 transition-all">
                {{ $ctaPrimaryText }}
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="{{ $ctaSecondaryUrl }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white hover:bg-white/10 transition-all">
                {{ $ctaSecondaryText }}
            </a>
        </div>
        @if(is_string($ctaNote) && trim($ctaNote) !== '')
            <p class="mt-6 text-sm text-primary-200">{{ $ctaNote }}</p>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
(function() {
    const shouldRun = () => {
        return document.body && document.body.dataset && document.body.dataset.mailpursePage === 'home-3';
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
        gsap.registerPlugin(ScrollTrigger);

        window.__mailpurseHomeGsapCtx = gsap.context(() => {
            document.querySelectorAll('article, .rounded-2xl').forEach(el => {
                gsap.from(el, {
                    scrollTrigger: { trigger: el, start: 'top 90%' },
                    opacity: 0, y: 30, duration: 0.6
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
