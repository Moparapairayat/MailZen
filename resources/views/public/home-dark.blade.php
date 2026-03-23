@extends('layouts.public')

@section('title', 'Self-Hosted Email Marketing Platform')
@section('pageId', 'home-dark')

@section('content')
<!-- Hero Section -->
<section class="relative min-h-[90vh] flex items-center overflow-hidden bg-gray-950">
    <!-- Subtle Grid -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b_1px,transparent_1px),linear-gradient(to_bottom,#1e293b_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
    
    <!-- Gradient Orbs - Animated -->
    <div class="hero-orb-1 absolute top-20 left-1/4 w-96 h-96 bg-primary-500/30 rounded-full blur-3xl"></div>
    <div class="hero-orb-2 absolute bottom-20 right-1/4 w-96 h-96 bg-violet-500/30 rounded-full blur-3xl"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div class="max-w-4xl mx-auto text-center">
            <!-- Badge -->
            <div class="hero-badge gsap-fade-up inline-flex items-center gap-2 rounded-full border border-primary-800 bg-primary-950/50 px-4 py-2 text-sm text-primary-300 mb-8">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
                <span class="font-medium">Self-Hosted</span>
                <span class="text-primary-600">·</span>
                <span>Your Data, Your Control</span>
            </div>
            
            <!-- Headline -->
            <h1 class="hero-headline gsap-fade-up text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-white leading-[1.1]">
                The email marketing platform
                <span class="block mt-2 bg-gradient-to-r from-primary-600 via-violet-600 to-primary-600 bg-clip-text text-transparent">you actually own</span>
            </h1>
            
            <!-- Subheadline -->
            <p class="hero-subheadline gsap-fade-up mt-8 text-lg sm:text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
                Host it yourself, run it as SaaS, or manage clients. MailZen gives you complete control over your email infrastructure with enterprise-grade features.
            </p>
            
            <!-- CTAs -->
            <div class="hero-ctas gsap-fade-up mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-white px-8 py-4 text-base font-semibold text-gray-900 shadow-xl shadow-white/10 hover:bg-gray-100 transition-all">
                    Get Started Free
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border border-gray-700 bg-gray-900 px-8 py-4 text-base font-semibold text-gray-300 hover:border-gray-600 hover:bg-gray-800 transition-all">
                    Explore Features
                </a>
            </div>
            
            <!-- Trust Indicators -->
            <div class="hero-trust gsap-fade-up mt-12 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-sm text-gray-400">
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
        </div>
    </div>
</section>

<!-- Logos Section -->
<section class="logos-section py-16 border-y border-gray-700 bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="logos-title gsap-fade-up text-center text-sm font-medium text-gray-400 mb-10">Integrates with your favorite email providers</p>
        <div class="logos-container flex flex-wrap items-center justify-center gap-x-16 gap-y-8">
            <!-- Amazon SES -->
            <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-300">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 1.5c-5.798 0-10.5 4.702-10.5 10.5s4.702 10.5 10.5 10.5 10.5-4.702 10.5-10.5-4.702-10.5-10.5-10.5zm0 19.5c-4.963 0-9-4.037-9-9s4.037-9 9-9 9 4.037 9 9-4.037 9-9 9z"/><path d="M8.25 15.75l3.75-3 3.75 3V8.25l-3.75 3-3.75-3z"/></svg>
                <span class="font-semibold text-lg">Amazon SES</span>
            </div>
            <!-- Mailgun -->
            <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-300">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                <span class="font-semibold text-lg">Mailgun</span>
            </div>
            <!-- SendGrid -->
            <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-300">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zm10-10h8v8h-8V3zm0 10h8v8h-8v-8z"/></svg>
                <span class="font-semibold text-lg">SendGrid</span>
            </div>
            <!-- Postmark -->
            <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-300">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                <span class="font-semibold text-lg">Postmark</span>
            </div>
            <!-- SMTP -->
            <div class="logo-item gsap-fade-up flex items-center gap-2 text-gray-300">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path fill="none" stroke="currentColor" stroke-width="1.5" d="M22 6l-10 7L2 6"/></svg>
                <span class="font-semibold text-lg">Any SMTP</span>
            </div>
        </div>
    </div>
</section>

<!-- Value Props -->
<section id="features" class="features-section py-24 lg:py-32 bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <h2 class="section-title gsap-fade-up text-3xl sm:text-4xl font-bold text-white">
                Everything you need to run email marketing at scale
            </h2>
            <p class="section-subtitle gsap-fade-up mt-4 text-lg text-gray-400">
                Whether you're sending for yourself or running a full SaaS business, MailZen has you covered.
            </p>
        </div>

        <div class="features-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-800 bg-gray-900 hover:border-primary-200 dark:hover:border-primary-800 hover:shadow-xl hover:shadow-primary-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Multi-Tenant SaaS Ready</h3>
                <p class="text-gray-400 leading-relaxed">Run your own email marketing SaaS. Manage customers, plans, billing, and permissions from a powerful admin panel.</p>
            </div>

            <!-- Feature 2 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-800 bg-gray-900 hover:border-violet-200 dark:hover:border-violet-800 hover:shadow-xl hover:shadow-violet-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Campaigns & Automation</h3>
                <p class="text-gray-400 leading-relaxed">Create one-time campaigns, recurring sends, or automated drip sequences. Drag-and-drop editor with responsive templates.</p>
            </div>

            <!-- Feature 3 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-800 bg-gray-900 hover:border-emerald-200 dark:hover:border-emerald-800 hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">List Management</h3>
                <p class="text-gray-400 leading-relaxed">Unlimited lists with custom fields, tags, and segments. Import/export CSV, double opt-in, and GDPR compliance built-in.</p>
            </div>

            <!-- Feature 4 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-800 bg-gray-900 hover:border-amber-200 dark:hover:border-amber-800 hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Multiple Delivery Servers</h3>
                <p class="text-gray-400 leading-relaxed">Connect Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, or any SMTP. Load balance and rotate for maximum deliverability.</p>
            </div>

            <!-- Feature 5 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-800 bg-gray-900 hover:border-rose-200 dark:hover:border-rose-800 hover:shadow-xl hover:shadow-rose-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Real-Time Analytics</h3>
                <p class="text-gray-400 leading-relaxed">Track opens, clicks, bounces, and unsubscribes in real-time. Detailed reports with geographic and device insights.</p>
            </div>

            <!-- Feature 6 -->
            <div class="feature-card gsap-fade-up group relative p-8 rounded-2xl border border-gray-800 bg-gray-900 hover:border-indigo-200 dark:hover:border-indigo-800 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Built-in Billing</h3>
                <p class="text-gray-400 leading-relaxed">Accept payments via Stripe, PayPal, or Paystack. Create plans, manage subscriptions, generate invoices automatically.</p>
            </div>
        </div>
    </div>
</section>

<!-- AI Section -->
<section class="py-24 lg:py-32 bg-gradient-to-b from-gray-900 to-gray-950 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#374151_1px,transparent_1px),linear-gradient(to_bottom,#374151_1px,transparent_1px)] bg-[size:3rem_3rem] opacity-20"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-gradient-to-b from-violet-500/15 to-transparent blur-3xl"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 rounded-full border border-violet-700 bg-violet-950/50 px-4 py-2 text-sm text-violet-300 mb-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                <span class="font-medium">AI-Powered</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold text-white">
                Write better emails with AI
            </h2>
            <p class="mt-4 text-lg text-gray-400">
                Generate compelling subject lines, email copy, and calls-to-action in seconds.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <!-- AI Feature 1 -->
            <div class="relative p-8 rounded-2xl bg-gray-900 border border-gray-800 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">AI Content Generator</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Describe what you want to say and let AI craft the perfect email copy. Supports multiple tones and styles.</p>
                    </div>
                </div>
            </div>

            <!-- AI Feature 2 -->
            <div class="relative p-8 rounded-2xl bg-gray-900 border border-gray-800 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">Subject Line Optimizer</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">Generate multiple subject line variations optimized for opens. A/B test with confidence.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-24 lg:py-32 bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">
                Get started in minutes
            </h2>
            <p class="mt-4 text-lg text-gray-400">
                Deploy on your own server and start sending emails right away.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Step 1 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">1</span>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Install & Configure</h3>
                <p class="text-gray-400">Upload to your server, run the installer, and configure your settings. Works on any PHP 8.2+ hosting.</p>
            </div>

            <!-- Step 2 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-violet-600 dark:text-violet-400">2</span>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Connect Email Providers</h3>
                <p class="text-gray-400">Add your delivery servers — Amazon SES, Mailgun, SendGrid, or any SMTP. Configure sending domains.</p>
            </div>

            <!-- Step 3 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">3</span>
                </div>
                <h3 class="text-xl font-semibold text-white mb-3">Start Sending</h3>
                <p class="text-gray-400">Create lists, import subscribers, design campaigns, and start sending. Or invite customers to your SaaS.</p>
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
<section class="pricing-section py-24 lg:py-32 bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 rounded-full border border-primary-700 bg-primary-950/50 px-4 py-2 text-sm text-primary-300 mb-6">
                <span class="font-medium">Our Pricing</span>
            </div>
            <h2 class="pricing-title gsap-fade-up text-3xl sm:text-4xl lg:text-5xl font-bold text-white italic">
                Choose Your Perfect Plan
            </h2>
            <p class="pricing-subtitle gsap-fade-up mt-4 text-lg text-gray-400">
                Pick the MailZen plan that fits your email marketing goals
            </p>
        </div>

        <!-- Billing Toggle -->
        <div class="flex items-center justify-center gap-4 mb-12" x-data="{ annual: true }">
            <span class="text-sm font-medium text-gray-400">Pay Monthly</span>
            <button @click="annual = !annual" class="relative w-14 h-7 bg-primary-600 rounded-full transition-colors">
                <span class="absolute top-1 left-1 w-5 h-5 bg-white rounded-full transition-transform" :class="{ 'translate-x-7': annual }"></span>
            </button>
            <span class="text-sm font-medium text-primary-400">Pay Annually <span class="text-primary-500">(save 20%)</span></span>
        </div>

        <!-- Pricing Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto" x-data="{ annual: true }">
            <!-- Starter Plan -->
            <div class="pricing-card gsap-fade-up relative bg-gray-950 rounded-3xl p-8 border border-gray-700 shadow-sm">
                <!-- Icon -->
                <div class="w-12 h-12 rounded-xl bg-primary-900/30 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                
                <!-- Plan Name -->
                <h3 class="text-xl font-bold text-white mb-2">Starter</h3>
                <p class="text-sm text-gray-400 mb-6">For individuals, and early-stage startups</p>
                
                <!-- Price -->
                <div class="mb-6">
                    <span class="text-4xl font-bold text-white">$19</span>
                    <span class="text-gray-400">/month</span>
                </div>
                
                <!-- CTA -->
                <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 w-full py-3 px-6 rounded-xl border border-gray-600 text-gray-300 font-medium hover:bg-gray-800 transition-colors mb-8">
                    Get Started
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                
                <!-- Features -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Features</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Up to 5,000 subscribers</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Basic email campaigns</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Email templates library</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Basic analytics & reports</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Growth Plan (Popular) -->
            <div class="pricing-card gsap-fade-up relative bg-gray-950 rounded-3xl p-8 border-2 border-primary-500 shadow-xl shadow-primary-500/10">
                <!-- Popular Badge -->
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-1.5 bg-primary-600 text-white text-sm font-semibold rounded-full">
                    Popular
                </div>
                
                <!-- Icon -->
                <div class="w-12 h-12 rounded-xl bg-primary-900/30 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                
                <!-- Plan Name -->
                <h3 class="text-xl font-bold text-white mb-2">Growth</h3>
                <p class="text-sm text-gray-400 mb-6">For individuals, and early-stage startups</p>
                
                <!-- Price -->
                <div class="mb-6">
                    <span class="text-4xl font-bold text-white">$49</span>
                    <span class="text-gray-400">/month</span>
                </div>
                
                <!-- CTA -->
                <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 w-full py-3 px-6 rounded-xl bg-primary-600 text-white font-medium hover:bg-primary-700 transition-colors mb-8">
                    Get Started
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                
                <!-- Features -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Features</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Up to 25,000 subscribers</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Advanced automation flows</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Real-time analytics with API</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>AI content generation tools</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Scale Plan -->
            <div class="pricing-card gsap-fade-up relative bg-gray-950 rounded-3xl p-8 border border-gray-700 shadow-sm">
                <!-- Icon -->
                <div class="w-12 h-12 rounded-xl bg-primary-900/30 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                
                <!-- Plan Name -->
                <h3 class="text-xl font-bold text-white mb-2">Scale</h3>
                <p class="text-sm text-gray-400 mb-6">For individuals, and early-stage startups</p>
                
                <!-- Price -->
                <div class="mb-6">
                    <span class="text-4xl font-bold text-white">$99</span>
                    <span class="text-gray-400">/month</span>
                </div>
                
                <!-- CTA -->
                <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 w-full py-3 px-6 rounded-xl border border-gray-600 text-gray-300 font-medium hover:bg-gray-800 transition-colors mb-8">
                    Get Started
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                
                <!-- Features -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Features</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Unlimited subscribers</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Priority chat & email support</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Dedicated onboarding specialist</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Dedicated success manager</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-24 lg:py-32 bg-gray-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">
                Frequently asked questions
            </h2>
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            <div class="rounded-2xl border border-gray-700 bg-gray-950 overflow-hidden">
                <button @click="open = open === 1 ? null : 1" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-white">What are the server requirements?</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-6 pb-5 text-gray-400">
                    PHP 8.2+, MySQL database, and a web server (Apache/Nginx). Redis is recommended for queues. Works on shared hosting, VPS, or dedicated servers.
                </div>
            </div>

            <div class="rounded-2xl border border-gray-700 bg-gray-950 overflow-hidden">
                <button @click="open = open === 2 ? null : 2" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-white">Can I run this as a SaaS for my clients?</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-6 pb-5 text-gray-400">
                    Absolutely! MailZen is built for multi-tenancy. Create customer accounts, define plans with limits, accept payments via Stripe/PayPal/Paystack, and let customers manage their own lists and campaigns.
                </div>
            </div>

            <div class="rounded-2xl border border-gray-700 bg-gray-950 overflow-hidden">
                <button @click="open = open === 3 ? null : 3" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-white">Which email providers are supported?</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-6 pb-5 text-gray-400">
                    Amazon SES, Mailgun, SendGrid, Postmark, SparkPost, and any standard SMTP server. You can configure multiple providers and rotate between them for better deliverability.
                </div>
            </div>

            <div class="rounded-2xl border border-gray-700 bg-gray-950 overflow-hidden">
                <button @click="open = open === 4 ? null : 4" class="w-full px-6 py-5 text-left flex items-center justify-between">
                    <span class="font-medium text-white">Is there a limit on subscribers or emails?</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-6 pb-5 text-gray-400">
                    No limits from our side. You can send as many emails as your server and email provider allow. The only limits are what you define in your customer plans.
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
            Take control of your email marketing
        </h2>
        <p class="mt-6 text-xl text-gray-400 max-w-2xl mx-auto">
            Stop paying monthly fees. Own your platform, own your data, and scale without limits.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-white px-8 py-4 text-base font-semibold text-gray-900 hover:bg-gray-100 transition-colors">
                Get Started Free
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="https://codecanyon.net/item/mailpurse-selfhosted-email-automation-marketing-saas/61213414" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border border-gray-700 px-8 py-4 text-base font-semibold text-white hover:bg-gray-800 transition-colors">
                View on CodeCanyon
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function() {
    const shouldRun = () => {
        return document.body && document.body.dataset && document.body.dataset.mailpursePage === 'home-dark';
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
