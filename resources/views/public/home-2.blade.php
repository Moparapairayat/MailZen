@extends('layouts.public')

@section('title', 'Self-Hosted Email Marketing Platform')
@section('pageId', 'home-2')

@section('content')
@php
    $navTheme = 'dark';

    try {
        $heroDescription = (string) \App\Models\Setting::get('home_2_hero_description', 'Build, automate, and scale your email marketing without the recurring costs. Self-host on your own server and keep 100% of your profits.');
        $heroScrollText = (string) \App\Models\Setting::get('home_2_hero_scroll_text', '');
        $heroButtonText = (string) \App\Models\Setting::get('home_2_hero_button_text', 'Start Free Trial');
        $heroButtonType = (string) \App\Models\Setting::get('home_2_hero_button_type', 'link');
        $heroButtonUrl = (string) \App\Models\Setting::get('home_2_hero_button_url', route('register'));
        $heroImagePath = (string) \App\Models\Setting::get('home_2_hero_image', '');
    } catch (\Throwable $e) {
        $heroDescription = 'Build, automate, and scale your email marketing without the recurring costs. Self-host on your own server and keep 100% of your profits.';
        $heroScrollText = '';
        $heroButtonText = 'Start Free Trial';
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
        $logoPaths = \App\Models\Setting::get('home_2_logos', []);
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
        $faqTitle = (string) \App\Models\Setting::get('home_faq_title', 'FAQs');
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
        $faqTitle = 'FAQs';
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
        $pricingTitle = (string) \App\Models\Setting::get('home_pricing_title', 'Simple pricing');
        $pricingSubtitle = (string) \App\Models\Setting::get('home_pricing_subtitle', 'Buy once. No monthly subscriptions.');
        $pricingPopularBadge = (string) \App\Models\Setting::get('home_pricing_popular_badge', 'Most Popular');
        $pricingCard1Title = (string) \App\Models\Setting::get('home_pricing_card_1_title', 'Starter');
        $pricingCard1Description = (string) \App\Models\Setting::get('home_pricing_card_1_description', 'For your own business or a single brand.');
        $pricingCard1CtaText = (string) \App\Models\Setting::get('home_pricing_card_1_cta_text', 'Get Started');
        $pricingCard2Title = (string) \App\Models\Setting::get('home_pricing_card_2_title', 'Agency / Reseller');
        $pricingCard2Description = (string) \App\Models\Setting::get('home_pricing_card_2_description', 'Built for multi-tenant + white-label setups.');
        $pricingCard2CtaText = (string) \App\Models\Setting::get('home_pricing_card_2_cta_text', 'Get MailZen');
        $pricingCard3Title = (string) \App\Models\Setting::get('home_pricing_card_3_title', 'Services');
        $pricingCard3Description = (string) \App\Models\Setting::get('home_pricing_card_3_description', 'Want installation, migration, or a custom feature?');
        $pricingCard3CtaText = (string) \App\Models\Setting::get('home_pricing_card_3_cta_text', 'View on CodeCanyon');

        $ctaBadge = (string) \App\Models\Setting::get('home_cta_badge', 'One-time license. Self-hosted.');
        $ctaTitle = (string) \App\Models\Setting::get('home_cta_title', 'Stop paying monthly fees');
        $ctaSubtitle = (string) \App\Models\Setting::get('home_cta_subtitle', 'Own your email marketing platform. One-time purchase, lifetime updates, unlimited potential.');
        $ctaPrimaryText = (string) \App\Models\Setting::get('home_cta_primary_text', 'Get MailZen for $29');
        $ctaPrimaryUrl = (string) \App\Models\Setting::get('home_cta_primary_url', route('register'));
        $ctaSecondaryText = (string) \App\Models\Setting::get('home_cta_secondary_text', 'View on CodeCanyon');
        $ctaSecondaryUrl = (string) \App\Models\Setting::get('home_cta_secondary_url', 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414');
        $ctaNote = (string) \App\Models\Setting::get('home_cta_note', '');
    } catch (\Throwable $e) {
        $pricingTitle = 'Simple pricing';
        $pricingSubtitle = 'Buy once. No monthly subscriptions.';
        $pricingPopularBadge = 'Most Popular';
        $pricingCard1Title = 'Starter';
        $pricingCard1Description = 'For your own business or a single brand.';
        $pricingCard1CtaText = 'Get Started';
        $pricingCard2Title = 'Agency / Reseller';
        $pricingCard2Description = 'Built for multi-tenant + white-label setups.';
        $pricingCard2CtaText = 'Get MailZen';
        $pricingCard3Title = 'Services';
        $pricingCard3Description = 'Want installation, migration, or a custom feature?';
        $pricingCard3CtaText = 'View on CodeCanyon';

        $ctaBadge = 'One-time license. Self-hosted.';
        $ctaTitle = 'Stop paying monthly fees';
        $ctaSubtitle = 'Own your email marketing platform. One-time purchase, lifetime updates, unlimited potential.';
        $ctaPrimaryText = 'Get MailZen for $29';
        $ctaPrimaryUrl = route('register');
        $ctaSecondaryText = 'View on CodeCanyon';
        $ctaSecondaryUrl = 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414';
        $ctaNote = '';
    }

    $ctaPrimaryUrl = is_string($ctaPrimaryUrl) && trim($ctaPrimaryUrl) !== '' ? $ctaPrimaryUrl : route('register');
    $ctaSecondaryUrl = is_string($ctaSecondaryUrl) && trim($ctaSecondaryUrl) !== '' ? $ctaSecondaryUrl : 'https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414';
@endphp

<!-- Hero Section - Split Layout with Stats -->
<section class="relative min-h-screen flex items-center overflow-hidden bg-slate-950">
    <!-- Animated Background -->
    <div class="absolute inset-0">
        <div class="absolute top-1/4 -left-20 w-[600px] h-[600px] bg-emerald-500/10 rounded-full blur-[150px]"></div>
        <div class="absolute bottom-1/4 -right-20 w-[500px] h-[500px] bg-cyan-500/10 rounded-full blur-[120px]"></div>
    </div>
    
    <!-- Dot Pattern -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.05)_1px,transparent_0)] bg-[size:40px_40px]"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <!-- Left Content -->
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-4 py-2 text-sm text-emerald-400 mb-8">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Now with AI-Powered Features</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight">
                    Send emails that
                    <span class="bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">convert</span>
                </h1>
                
                <p class="mt-8 text-xl text-slate-400 leading-relaxed">
                    {{ $heroDescription }}
                </p>
                
                <!-- Stats Row -->
                <div class="mt-10 grid grid-cols-3 gap-8">
                    <div>
                        <div class="text-3xl font-bold text-white">10M+</div>
                        <div class="text-sm text-slate-500">Emails Sent</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">99.9%</div>
                        <div class="text-sm text-slate-500">Uptime</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">$0</div>
                        <div class="text-sm text-slate-500">Monthly Fees</div>
                    </div>
                </div>
                
                <!-- CTA Buttons -->
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="{{ $heroButtonUrl }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all">
                        {{ $heroButtonText }}
                        @if($heroButtonType === 'video')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        @endif
                    </a>
                    <a href="#comparison" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/50 px-8 py-4 text-base font-semibold text-white hover:bg-slate-800 transition-all">
                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        See Comparison
                    </a>
                </div>

                @if(is_string($heroScrollText) && trim($heroScrollText) !== '')
                    <p class="mt-4 text-sm text-slate-500">{{ $heroScrollText }}</p>
                @endif
            </div>
            
            <!-- Right - Email Preview Card -->
            <div class="relative hidden lg:block">
                @if(!empty($heroImageUrl))
                    <img src="{{ $heroImageUrl }}" alt="" class="relative max-w-full rounded-3xl border border-slate-700/50 shadow-2xl">
                @else
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-500/20 rounded-3xl blur-2xl"></div>
                    <div class="relative bg-slate-900/80 backdrop-blur-xl rounded-3xl border border-slate-700/50 p-6 shadow-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white font-bold">M</div>
                                <div>
                                    <div class="text-white font-medium">MailZen Campaign</div>
                                    <div class="text-sm text-slate-500">to: 50,000 subscribers</div>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-medium">Sent</span>
                        </div>
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-3 rounded-xl bg-slate-800/50">
                                <div class="text-2xl font-bold text-white">68%</div>
                                <div class="text-xs text-slate-500">Open Rate</div>
                            </div>
                            <div class="text-center p-3 rounded-xl bg-slate-800/50">
                                <div class="text-2xl font-bold text-white">24%</div>
                                <div class="text-xs text-slate-500">Click Rate</div>
                            </div>
                            <div class="text-center p-3 rounded-xl bg-slate-800/50">
                                <div class="text-2xl font-bold text-white">$12K</div>
                                <div class="text-xs text-slate-500">Revenue</div>
                            </div>
                            <div class="text-center p-3 rounded-xl bg-slate-800/50">
                                <div class="text-2xl font-bold text-white">0.2%</div>
                                <div class="text-xs text-slate-500">Bounce</div>
                            </div>
                        </div>
                        <div class="h-24 flex items-end gap-1">
                            <div class="flex-1 bg-gradient-to-t from-emerald-500/50 to-emerald-500/10 rounded-t" style="height: 40%"></div>
                            <div class="flex-1 bg-gradient-to-t from-emerald-500/50 to-emerald-500/10 rounded-t" style="height: 55%"></div>
                            <div class="flex-1 bg-gradient-to-t from-emerald-500/50 to-emerald-500/10 rounded-t" style="height: 45%"></div>
                            <div class="flex-1 bg-gradient-to-t from-emerald-500/50 to-emerald-500/10 rounded-t" style="height: 70%"></div>
                            <div class="flex-1 bg-gradient-to-t from-emerald-500/50 to-emerald-500/10 rounded-t" style="height: 60%"></div>
                            <div class="flex-1 bg-gradient-to-t from-emerald-500/50 to-emerald-500/10 rounded-t" style="height: 85%"></div>
                            <div class="flex-1 bg-gradient-to-t from-cyan-500/50 to-cyan-500/10 rounded-t" style="height: 100%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Brands Marquee -->
<section class="py-8 bg-slate-950 border-y border-slate-800 overflow-hidden">
    @if(isset($logoUrls) && count($logoUrls) > 0)
        <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6 px-4">
            @foreach($logoUrls as $url)
                <img src="{{ $url }}" alt="" class="h-10 w-auto opacity-80 hover:opacity-100 transition-opacity">
            @endforeach
        </div>
    @else
        <div class="flex items-center justify-center gap-16 text-slate-600 font-semibold">
            <span>Amazon SES</span>
            <span>•</span>
            <span>Mailgun</span>
            <span>•</span>
            <span>SendGrid</span>
            <span>•</span>
            <span>Postmark</span>
            <span>•</span>
            <span>SparkPost</span>
            <span>•</span>
            <span>Any SMTP</span>
        </div>
    @endif
</section>

<!-- Why Choose - Bento Grid -->
<section id="features" class="py-24 lg:py-32 bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">Why businesses choose MailZen</h2>
            <p class="mt-4 text-lg text-slate-400">Everything you need to run professional email marketing</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Large Card -->
            <div class="lg:col-span-2 p-8 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700/50">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-3">Complete White-Label Solution</h3>
                <p class="text-slate-400 leading-relaxed">Rebrand everything - from the dashboard to emails. Your clients will never know you're using MailZen. Perfect for agencies and resellers.</p>
            </div>
            
            <!-- Small Card -->
            <div class="p-8 rounded-3xl bg-gradient-to-br from-cyan-950 to-slate-900 border border-cyan-900/50">
                <div class="w-14 h-14 rounded-2xl bg-cyan-500/20 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Lightning Fast</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Send millions of emails per hour with optimized queue processing.</p>
            </div>
            
            <!-- Small Card -->
            <div class="p-8 rounded-3xl bg-gradient-to-br from-purple-950 to-slate-900 border border-purple-900/50">
                <div class="w-14 h-14 rounded-2xl bg-purple-500/20 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">AI Writing Assistant</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Generate compelling subject lines and email copy with built-in AI.</p>
            </div>
            
            <!-- Large Card -->
            <div class="lg:col-span-2 p-8 rounded-3xl bg-gradient-to-br from-amber-950 to-slate-900 border border-amber-900/50">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-3">Built-in Monetization</h3>
                <p class="text-slate-400 leading-relaxed">Accept payments via Stripe, PayPal, or Paystack. Create subscription plans, manage billing cycles, and generate invoices automatically.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-20 lg:py-28 bg-slate-900" x-data="{ tab: 'delivery' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">A platform you can customize</h2>
            <p class="mt-4 text-lg text-slate-400">Explore core areas in seconds. Built to be simple now, powerful later.</p>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="p-2 rounded-2xl bg-slate-950 border border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-2">
                <button type="button" @click="tab = 'delivery'" :class="tab === 'delivery' ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'bg-transparent text-slate-300 border-transparent hover:bg-slate-900/60'" class="inline-flex items-center justify-center px-4 py-3 rounded-xl border text-sm font-semibold transition-colors">Delivery</button>
                <button type="button" @click="tab = 'automation'" :class="tab === 'automation' ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30' : 'bg-transparent text-slate-300 border-transparent hover:bg-slate-900/60'" class="inline-flex items-center justify-center px-4 py-3 rounded-xl border text-sm font-semibold transition-colors">Automation</button>
                <button type="button" @click="tab = 'saas'" :class="tab === 'saas' ? 'bg-purple-500/15 text-purple-300 border-purple-500/30' : 'bg-transparent text-slate-300 border-transparent hover:bg-slate-900/60'" class="inline-flex items-center justify-center px-4 py-3 rounded-xl border text-sm font-semibold transition-colors">SaaS & Billing</button>
            </div>

            <div class="mt-6 rounded-3xl border border-slate-800 bg-slate-950">
                <div class="p-8 sm:p-10" x-show="tab === 'delivery'" x-transition>
                    <h3 class="text-2xl font-bold text-white">Connect multiple delivery providers</h3>
                    <p class="mt-3 text-slate-400">Use Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, ZeptoMail, or any SMTP server.</p>
                    <div class="mt-6 grid sm:grid-cols-2 gap-3 text-sm text-slate-300">
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-400"></span><span>Primary server + failover friendly setup</span></div>
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-400"></span><span>Delivery testing and diagnostics</span></div>
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-400"></span><span>Separate transactional vs marketing routing</span></div>
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-400"></span><span>Tracking + bounce processing ready</span></div>
                    </div>
                </div>

                <div class="p-8 sm:p-10" x-show="tab === 'automation'" x-transition style="display: none;">
                    <h3 class="text-2xl font-bold text-white">Automations without complexity</h3>
                    <p class="mt-3 text-slate-400">Run sequences, autoresponders, and list targeting with predictable performance.</p>
                    <div class="mt-6 grid sm:grid-cols-2 gap-3 text-sm text-slate-300">
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-cyan-400"></span><span>Drip sequences & autoresponders</span></div>
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-cyan-400"></span><span>Scheduling + segmentation</span></div>
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-cyan-400"></span><span>Import and validation workflows</span></div>
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-cyan-400"></span><span>Reports that are easy to read</span></div>
                    </div>
                </div>

                <div class="p-8 sm:p-10" x-show="tab === 'saas'" x-transition style="display: none;">
                    <h3 class="text-2xl font-bold text-white">Launch your own SaaS</h3>
                    <p class="mt-3 text-slate-400">Plans, limits, billing integrations, and customer access controls are built in.</p>
                    <div class="mt-6 grid sm:grid-cols-2 gap-3 text-sm text-slate-300">
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-purple-400"></span><span>Stripe / PayPal / Paystack</span></div>
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-purple-400"></span><span>Plans, quotas, and permissions</span></div>
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-purple-400"></span><span>White-label for agencies</span></div>
                        <div class="flex gap-2 items-start"><span class="mt-1 w-2 h-2 rounded-full bg-purple-400"></span><span>Customer dashboards</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Comparison Table -->
<section id="comparison" class="py-24 lg:py-32 bg-slate-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">MailZen vs. Monthly SaaS</h2>
            <p class="mt-4 text-lg text-slate-400">See how much you save with a one-time purchase</p>
        </div>
        
        <div class="rounded-3xl border border-slate-800 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-800">
                        <th class="px-6 py-5 text-left text-sm font-medium text-slate-400">Feature</th>
                        <th class="px-6 py-5 text-center text-sm font-medium text-emerald-400">MailZen</th>
                        <th class="px-6 py-5 text-center text-sm font-medium text-slate-400">Mailchimp</th>
                        <th class="px-6 py-5 text-center text-sm font-medium text-slate-400">ConvertKit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <tr class="bg-slate-900/50">
                        <td class="px-6 py-5 text-white">10,000 subscribers</td>
                        <td class="px-6 py-5 text-center text-emerald-400 font-bold">$29 once</td>
                        <td class="px-6 py-5 text-center text-slate-400">$100/mo</td>
                        <td class="px-6 py-5 text-center text-slate-400">$119/mo</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-5 text-white">Unlimited emails</td>
                        <td class="px-6 py-5 text-center"><svg class="w-6 h-6 text-emerald-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></td>
                        <td class="px-6 py-5 text-center text-slate-500">Limited</td>
                        <td class="px-6 py-5 text-center"><svg class="w-6 h-6 text-emerald-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></td>
                    </tr>
                    <tr class="bg-slate-900/50">
                        <td class="px-6 py-5 text-white">White-label</td>
                        <td class="px-6 py-5 text-center"><svg class="w-6 h-6 text-emerald-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></td>
                        <td class="px-6 py-5 text-center"><svg class="w-6 h-6 text-slate-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></td>
                        <td class="px-6 py-5 text-center"><svg class="w-6 h-6 text-slate-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></td>
                    </tr>
                    <tr>
                        <td class="px-6 py-5 text-white">Self-hosted</td>
                        <td class="px-6 py-5 text-center"><svg class="w-6 h-6 text-emerald-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></td>
                        <td class="px-6 py-5 text-center"><svg class="w-6 h-6 text-slate-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></td>
                        <td class="px-6 py-5 text-center"><svg class="w-6 h-6 text-slate-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></td>
                    </tr>
                    <tr class="bg-slate-800">
                        <td class="px-6 py-5 text-white font-bold">Annual cost (10K subs)</td>
                        <td class="px-6 py-5 text-center text-emerald-400 font-bold text-xl">$29</td>
                        <td class="px-6 py-5 text-center text-slate-400 font-bold">$1,200</td>
                        <td class="px-6 py-5 text-center text-slate-400 font-bold">$1,428</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- How It Works Timeline -->
<section class="py-24 lg:py-32 bg-slate-950">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">Up and running in 3 steps</h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-emerald-400">1</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Deploy</h3>
                <p class="text-slate-400">Upload to any PHP 8.2+ server, run the installer, configure your database.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-cyan-500/20 flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-cyan-400">2</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Connect</h3>
                <p class="text-slate-400">Add Amazon SES, Mailgun, SendGrid, or any SMTP server for delivery.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-purple-500/20 flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-purple-400">3</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Launch</h3>
                <p class="text-slate-400">Create campaigns, import subscribers, or invite customers to your SaaS.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing -->
<section id="pricing" class="py-24 lg:py-32 bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">{{ $pricingTitle }}</h2>
            <p class="mt-4 text-lg text-slate-400">{{ $pricingSubtitle }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-3xl border border-slate-800 bg-slate-950 p-8">
                <div class="text-sm font-semibold text-slate-300">{{ $pricingCard1Title }}</div>
                <div class="mt-2 flex items-end gap-2">
                    <div class="text-4xl font-bold text-white">$29</div>
                    <div class="text-sm text-slate-500 pb-1">one-time</div>
                </div>
                <p class="mt-4 text-slate-400">{{ $pricingCard1Description }}</p>
                <ul class="mt-6 space-y-3 text-sm text-slate-300">
                    <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-400"></span><span>Unlimited emails (provider limits apply)</span></li>
                    <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-400"></span><span>Campaigns, automations, templates</span></li>
                    <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-400"></span><span>Multiple delivery servers</span></li>
                </ul>
                <a href="{{ route('register') }}" class="mt-8 inline-flex w-full items-center justify-center rounded-2xl bg-slate-800 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">{{ $pricingCard1CtaText }}</a>
            </div>

            <div class="rounded-3xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/10 to-cyan-500/10 p-8 relative overflow-hidden">
                <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl"></div>
                <div class="relative">
                    <div class="inline-flex items-center rounded-full bg-emerald-500/15 border border-emerald-500/25 px-3 py-1 text-xs font-semibold text-emerald-300">{{ $pricingPopularBadge }}</div>
                    <div class="mt-4 text-sm font-semibold text-slate-200">{{ $pricingCard2Title }}</div>
                    <div class="mt-2 flex items-end gap-2">
                        <div class="text-4xl font-bold text-white">$29</div>
                        <div class="text-sm text-slate-400 pb-1">one-time</div>
                    </div>
                    <p class="mt-4 text-slate-300">{{ $pricingCard2Description }}</p>
                    <ul class="mt-6 space-y-3 text-sm text-slate-200">
                        <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-300"></span><span>White-label branding</span></li>
                        <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-300"></span><span>Customers, plans, and limits</span></li>
                        <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-emerald-300"></span><span>Billing integrations included</span></li>
                    </ul>
                    <a href="{{ route('register') }}" class="mt-8 inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/35 transition-all">{{ $pricingCard2CtaText }}</a>
                    <div class="mt-4 text-center text-xs text-slate-400">One-time purchase. Self-hosted.</div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-800 bg-slate-950 p-8">
                <div class="text-sm font-semibold text-slate-300">Need help?</div>
                <div class="mt-2 text-2xl font-bold text-white">{{ $pricingCard3Title }}</div>
                <p class="mt-4 text-slate-400">{{ $pricingCard3Description }}</p>
                <ul class="mt-6 space-y-3 text-sm text-slate-300">
                    <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-cyan-400"></span><span>Installation & configuration</span></li>
                    <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-cyan-400"></span><span>Custom integrations</span></li>
                    <li class="flex gap-3"><span class="mt-1 w-2 h-2 rounded-full bg-cyan-400"></span><span>Priority support options</span></li>
                </ul>
                <a href="https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414" target="_blank" class="mt-8 inline-flex w-full items-center justify-center rounded-2xl border border-slate-700 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">{{ $pricingCard3CtaText }}</a>
            </div>
        </div>
    </div>
</section>

<!-- FAQs -->
<section id="faqs" class="py-24 lg:py-32 bg-slate-950">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">{{ $faqTitle }}</h2>
            @if(is_string($faqSubtitle) && trim($faqSubtitle) !== '')
                <p class="mt-4 text-lg text-slate-400">{{ $faqSubtitle }}</p>
            @endif
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 overflow-hidden">
                <button type="button" @click="open = open === 1 ? null : 1" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-white">{{ $faq1Question }}</span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-6 pb-5 text-slate-400">
                    {{ $faq1Answer }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 overflow-hidden">
                <button type="button" @click="open = open === 2 ? null : 2" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-white">{{ $faq2Question }}</span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-6 pb-5 text-slate-400">
                    {{ $faq2Answer }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 overflow-hidden">
                <button type="button" @click="open = open === 3 ? null : 3" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-white">{{ $faq3Question }}</span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-6 pb-5 text-slate-400">
                    {{ $faq3Answer }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 overflow-hidden">
                <button type="button" @click="open = open === 4 ? null : 4" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-white">{{ $faq4Question }}</span>
                    <svg class="w-5 h-5 text-slate-400 transition-transform" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-6 pb-5 text-slate-400">
                    {{ $faq4Answer }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonial -->
<section class="py-24 lg:py-32 bg-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <svg class="w-12 h-12 text-slate-700 mx-auto mb-8" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
        <blockquote class="text-2xl sm:text-3xl font-medium text-white leading-relaxed">
            "Switched from Mailchimp and saved over $1,000 in the first year alone. The white-label feature lets me resell to my clients."
        </blockquote>
        <div class="mt-8">
            <div class="font-semibold text-white">Digital Agency Owner</div>
            <div class="text-sm text-slate-500">Managing 25+ client accounts</div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="py-24 lg:py-32 bg-gradient-to-br from-slate-950 via-emerald-950/20 to-slate-950 relative overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.06)_1px,transparent_0)] bg-[size:34px_34px]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[480px] bg-emerald-500/12 rounded-full blur-[110px]"></div>
        <div class="absolute -top-24 -right-24 w-[520px] h-[520px] bg-cyan-500/10 rounded-full blur-[120px]"></div>
    </div>
    
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="rounded-3xl border border-slate-800 bg-slate-950/40 backdrop-blur-xl px-6 py-10 sm:px-10 sm:py-12">
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-4 py-2 text-sm text-emerald-300">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>{{ $ctaBadge }}</span>
            </div>

            <h2 class="mt-6 text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight">
                {{ $ctaTitle }}
            </h2>
            <p class="mt-5 text-lg sm:text-xl text-slate-300/90 max-w-2xl mx-auto">
                {{ $ctaSubtitle }}
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3">
                <a href="{{ $ctaPrimaryUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/35 transition-all">
                    {{ $ctaPrimaryText }}
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ $ctaSecondaryUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-700 bg-slate-900/40 px-8 py-4 text-base font-semibold text-white hover:bg-slate-800 transition-colors">
                    {{ $ctaSecondaryText }}
                </a>
            </div>

            <div class="mt-6 text-sm text-slate-400">
                @if(is_string($ctaNote) && trim($ctaNote) !== '')
                    {{ $ctaNote }}
                @else
                    Prefer CodeCanyon?
                    <a href="{{ $ctaSecondaryUrl }}" target="_blank" class="text-emerald-300 hover:text-emerald-200 underline underline-offset-4">Open listing</a>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function() {
    const shouldRun = () => {
        return document.body && document.body.dataset && document.body.dataset.mailpursePage === 'home-2';
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
            document.querySelectorAll('.gsap-fade-up').forEach(el => {
                gsap.to(el, {
                    scrollTrigger: { trigger: el, start: 'top 85%' },
                    opacity: 1, y: 0, duration: 0.6
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
