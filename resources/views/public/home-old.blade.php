@extends('layouts.public')

@section('title', 'Email Marketing Platform')

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-white via-primary-50/30 to-white dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-primary-200/50 blur-3xl dark:bg-primary-500/10"></div>
        <div class="absolute -bottom-40 -left-40 h-[500px] w-[500px] rounded-full bg-indigo-200/50 blur-3xl dark:bg-indigo-500/10"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[600px] w-[600px] rounded-full bg-gradient-to-br from-primary-100/40 to-indigo-100/40 blur-3xl dark:from-primary-500/5 dark:to-indigo-500/5"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 lg:pt-32 lg:pb-32">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 rounded-full bg-primary-100 dark:bg-primary-900/30 px-4 py-1.5 text-sm font-medium text-primary-700 dark:text-primary-300 mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                </span>
                Trusted by 10,000+ businesses worldwide
            </div>
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl">
                Send emails that
                <span class="relative whitespace-nowrap">
                    <svg aria-hidden="true" viewBox="0 0 418 42" class="absolute left-0 top-2/3 h-[0.58em] w-full fill-primary-300/70 dark:fill-primary-500/30" preserveAspectRatio="none"><path d="M203.371.916c-26.013-2.078-76.686 1.963-124.73 9.946L67.3 12.749C35.421 18.062 18.2 21.766 6.004 25.934 1.244 27.561.828 27.778.874 28.61c.07 1.214.828 1.121 9.595-1.176 9.072-2.377 17.15-3.92 39.246-7.496C123.565 7.986 157.869 4.492 195.942 5.046c7.461.108 19.25 1.696 19.17 2.582-.107 1.183-7.874 4.31-25.75 10.366-21.992 7.45-35.43 12.534-36.701 13.884-2.173 2.308-.202 4.407 4.442 4.734 2.654.187 3.263.157 15.593-.78 35.401-2.686 57.944-3.488 88.365-3.143 46.327.526 75.721 2.23 130.788 7.584 19.787 1.924 20.814 1.98 24.557 1.332l.066-.011c1.201-.203 1.53-1.825.399-2.335-2.911-1.31-4.893-1.604-22.048-3.261-57.509-5.556-87.871-7.36-132.059-7.842-23.239-.254-33.617-.116-50.627.674-11.629.54-42.371 2.494-46.696 2.967-2.359.259 8.133-3.625 26.504-9.81 23.239-7.825 27.934-10.149 28.304-14.005.417-4.348-3.529-6-16.878-7.066Z"></path></svg>
                    <span class="relative text-primary-600 dark:text-primary-400">convert</span>
                </span>
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                The all-in-one email marketing platform that helps you create stunning campaigns, automate your workflows, and grow your audience — without the complexity.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-primary-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-primary-500/25 hover:bg-primary-700 hover:shadow-xl hover:shadow-primary-500/30 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    Start Free Trial
                    <svg class="ml-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('pricing') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border-2 border-gray-200 bg-white px-8 py-4 text-base font-semibold text-gray-900 hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700">
                    <svg class="mr-2 h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <polygon points="10 8 16 12 10 16 10 8" />
                    </svg>
                    Watch Demo
                </a>
            </div>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                No credit card required · Free 14-day trial · Cancel anytime
            </p>
        </div>

        <!-- Hero Stats -->
        <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-4 max-w-3xl mx-auto">
            <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 p-6 text-center">
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white">99.9%</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Delivery Rate</div>
            </div>
            <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 p-6 text-center">
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white">45%</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Avg. Open Rate</div>
            </div>
            <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 p-6 text-center">
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white">10M+</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Emails Sent</div>
            </div>
            <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 p-6 text-center">
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white">24/7</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Expert Support</div>
            </div>
        </div>

        <!-- Hero Dashboard Preview -->
        <div class="mt-16 relative max-w-5xl mx-auto">
            <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-primary-500/20 via-indigo-500/20 to-purple-500/20 blur-2xl dark:from-primary-500/10 dark:via-indigo-500/10 dark:to-purple-500/10"></div>
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                    <span class="h-3 w-3 rounded-full bg-red-400"></span>
                    <span class="h-3 w-3 rounded-full bg-amber-400"></span>
                    <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                    <div class="ml-4 flex-1 h-6 rounded-md bg-white dark:bg-gray-900 flex items-center px-3">
                        <span class="text-xs text-gray-400">{{ config('app.url') }}/dashboard</span>
                    </div>
                </div>
                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-12 gap-6">
                        <div class="col-span-3 hidden lg:block">
                            <div class="space-y-2">
                                <div class="h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center px-3">
                                    <div class="h-4 w-4 rounded bg-primary-500"></div>
                                    <div class="ml-2 h-3 w-16 rounded bg-primary-200 dark:bg-primary-800"></div>
                                </div>
                                <div class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                                <div class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                                <div class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                                <div class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                        </div>
                        <div class="col-span-12 lg:col-span-9">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm dark:border-gray-800 dark:from-emerald-900/20 dark:to-gray-900">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Subscribers</div>
                                        <div class="flex items-center text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                            <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            12%
                                        </div>
                                    </div>
                                    <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">24,521</div>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-primary-50 to-white p-4 shadow-sm dark:border-gray-800 dark:from-primary-900/20 dark:to-gray-900">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Open Rate</div>
                                        <div class="flex items-center text-xs font-medium text-primary-600 dark:text-primary-400">
                                            <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            8%
                                        </div>
                                    </div>
                                    <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">45.2%</div>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-indigo-50 to-white p-4 shadow-sm dark:border-gray-800 dark:from-indigo-900/20 dark:to-gray-900">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Click Rate</div>
                                        <div class="flex items-center text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                            <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            15%
                                        </div>
                                    </div>
                                    <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">12.8%</div>
                                </div>
                            </div>
                            <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Campaign Performance</div>
                                    <div class="flex items-center gap-4 text-xs">
                                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-primary-500"></span> Opens</span>
                                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-indigo-500"></span> Clicks</span>
                                    </div>
                                </div>
                                <svg class="h-32 w-full" viewBox="0 0 600 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <linearGradient id="gradient1" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" style="stop-color:#2563eb;stop-opacity:0.3" />
                                            <stop offset="100%" style="stop-color:#2563eb;stop-opacity:0" />
                                        </linearGradient>
                                    </defs>
                                    <path d="M0 100 C50 80, 100 90, 150 70 C200 50, 250 60, 300 40 C350 25, 400 35, 450 20 C500 10, 550 25, 600 15 L600 120 L0 120 Z" fill="url(#gradient1)" />
                                    <path d="M0 100 C50 80, 100 90, 150 70 C200 50, 250 60, 300 40 C350 25, 400 35, 450 20 C500 10, 550 25, 600 15" stroke="#2563eb" stroke-width="3" stroke-linecap="round" fill="none" />
                                    <path d="M0 110 C50 95, 100 100, 150 85 C200 70, 250 80, 300 65 C350 55, 400 60, 450 50 C500 40, 550 50, 600 40" stroke="#6366f1" stroke-width="2" stroke-linecap="round" fill="none" opacity="0.7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="relative overflow-hidden bg-white py-16 dark:bg-gray-900 lg:py-24">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-28 left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-primary-200/35 blur-3xl dark:bg-primary-500/10"></div>
        <div class="absolute -bottom-40 -right-40 h-[520px] w-[520px] rounded-full bg-indigo-200/30 blur-3xl dark:bg-indigo-500/10"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                Everything You Need to Scale
                <span class="block">Smarter and Grow Faster</span>
            </h2>
            <p class="mt-4 text-base text-gray-600 dark:text-gray-300 sm:text-lg">
                A complete set of powerful tools to create, manage, automate, and optimize high-performing email campaigns — all from one simple platform.
            </p>
        </div>

        <div class="mt-12 grid grid-cols-1 gap-6 sm:mt-16 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group flex flex-col rounded-2xl border border-gray-200 bg-white/70 p-6 shadow-sm backdrop-blur-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900/40">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-b from-gray-50 to-white shadow-sm dark:border-gray-800 dark:from-gray-800/40 dark:to-gray-900">
                    <div class="aspect-[16/10] p-4">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-400/80"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400/80"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400/80"></span>
                            <div class="ml-3 h-7 flex-1 rounded-md bg-white/70 dark:bg-gray-900/40"></div>
                        </div>
                        <div class="mt-4 grid grid-cols-12 gap-3">
                            <div class="col-span-4 space-y-2">
                                <div class="h-8 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                                <div class="h-8 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                                <div class="h-8 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                            <div class="col-span-8">
                                <div class="h-16 rounded-xl bg-primary-100/70 dark:bg-primary-500/10"></div>
                                <div class="mt-3 h-10 rounded-xl bg-gray-100 dark:bg-gray-800"></div>
                                <div class="mt-3 h-10 rounded-xl bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <h3 class="mt-6 text-xl font-extrabold text-gray-900 dark:text-white">Email Lists.</h3>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Organize, segment, and maintain clean subscriber lists.</p>
                <div class="mt-auto pt-4">
                    <a href="{{ route('features') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-primary-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-primary-300 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 12h16" />
                            <path d="M12 4v16" />
                        </svg>
                        Smart Lists
                    </a>
                </div>
            </div>

            <div class="group flex flex-col rounded-2xl border border-gray-200 bg-white/70 p-6 shadow-sm backdrop-blur-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900/40">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-b from-gray-50 to-white shadow-sm dark:border-gray-800 dark:from-gray-800/40 dark:to-gray-900">
                    <div class="aspect-[16/10] p-4">
                        <div class="flex items-center justify-between">
                            <div class="h-7 w-2/5 rounded-md bg-gray-100 dark:bg-gray-800"></div>
                            <div class="h-7 w-20 rounded-md bg-primary-600/10 dark:bg-primary-500/10"></div>
                        </div>
                        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <div class="h-3 w-24 rounded bg-gray-100 dark:bg-gray-800"></div>
                                <div class="h-3 w-14 rounded bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                            <div class="mt-3 h-10 rounded-lg bg-primary-100/70 dark:bg-primary-500/10"></div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div class="h-10 rounded-xl bg-gray-100 dark:bg-gray-800"></div>
                            <div class="h-10 rounded-xl bg-gray-100 dark:bg-gray-800"></div>
                        </div>
                    </div>
                </div>
                <h3 class="mt-6 text-xl font-extrabold text-gray-900 dark:text-white">Email Campaigns.</h3>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Create responsive emails and track results instantly.</p>
                <div class="mt-auto pt-4">
                    <a href="{{ route('features') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-primary-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-primary-300 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16v16H4z" />
                            <path d="M8 8h8" />
                            <path d="M8 12h6" />
                            <path d="M8 16h5" />
                        </svg>
                        Campaign Builder
                    </a>
                </div>
            </div>

            <div class="group flex flex-col rounded-2xl border border-gray-200 bg-white/70 p-6 shadow-sm backdrop-blur-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900/40">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-b from-gray-50 to-white shadow-sm dark:border-gray-800 dark:from-gray-800/40 dark:to-gray-900">
                    <div class="aspect-[16/10] p-4">
                        <div class="flex items-center justify-between">
                            <div class="h-7 w-1/3 rounded-md bg-gray-100 dark:bg-gray-800"></div>
                            <div class="h-7 w-24 rounded-md bg-indigo-600/10 dark:bg-indigo-500/10"></div>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div class="h-10 rounded-xl bg-gray-100 dark:bg-gray-800"></div>
                            <div class="h-10 rounded-xl bg-gray-100 dark:bg-gray-800"></div>
                            <div class="h-10 rounded-xl bg-gray-100 dark:bg-gray-800"></div>
                        </div>
                    </div>
                </div>
                <h3 class="mt-6 text-xl font-extrabold text-gray-900 dark:text-white">Auto Responders.</h3>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Automate emails with smart, behavior-based triggers.</p>
                <div class="mt-auto pt-4">
                    <a href="{{ route('features') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-primary-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-primary-300 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v4" />
                            <path d="M12 18v4" />
                            <path d="M4.93 4.93l2.83 2.83" />
                            <path d="M16.24 16.24l2.83 2.83" />
                            <path d="M2 12h4" />
                            <path d="M18 12h4" />
                            <path d="M4.93 19.07l2.83-2.83" />
                            <path d="M16.24 7.76l2.83-2.83" />
                        </svg>
                        Automation
                    </a>
                </div>
            </div>

            <div class="group flex flex-col rounded-2xl border border-gray-200 bg-white/70 p-6 shadow-sm backdrop-blur-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900/40">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-b from-gray-50 to-white shadow-sm dark:border-gray-800 dark:from-gray-800/40 dark:to-gray-900">
                    <div class="aspect-[16/10] p-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="h-3 w-2/3 rounded bg-gray-100 dark:bg-gray-800"></div>
                                <div class="mt-2 h-5 w-1/2 rounded bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="h-3 w-2/3 rounded bg-gray-100 dark:bg-gray-800"></div>
                                <div class="mt-2 h-5 w-1/2 rounded bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="h-3 w-2/3 rounded bg-gray-100 dark:bg-gray-800"></div>
                                <div class="mt-2 h-5 w-1/2 rounded bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                        </div>
                        <div class="mt-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <div class="h-3 w-24 rounded bg-gray-100 dark:bg-gray-800"></div>
                                <div class="h-3 w-16 rounded bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                            <svg class="mt-4 h-12 w-full" viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 45 C25 25, 50 55, 80 35 C105 18, 130 40, 150 24 C170 12, 185 22, 200 10" stroke="#2563eb" stroke-width="4" stroke-linecap="round" />
                            </svg>
                        </div>
                    </div>
                </div>
                <h3 class="mt-6 text-xl font-extrabold text-gray-900 dark:text-white">Analytics & Tracking.</h3>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Track engagement and performance in real time.</p>
                <div class="mt-auto pt-4">
                    <a href="{{ route('features') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-primary-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-primary-300 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3v18h18" />
                            <path d="M7 14l4-4 3 3 6-6" />
                        </svg>
                        Live Analytics
                    </a>
                </div>
            </div>

            <div class="group flex flex-col rounded-2xl border border-gray-200 bg-white/70 p-6 shadow-sm backdrop-blur-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900/40">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-b from-gray-50 to-white shadow-sm dark:border-gray-800 dark:from-gray-800/40 dark:to-gray-900">
                    <div class="aspect-[16/10] p-4">
                        <div class="h-7 w-28 rounded-md bg-gray-100 dark:bg-gray-800"></div>
                        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="space-y-2">
                                <div class="h-3 w-2/3 rounded bg-gray-100 dark:bg-gray-800"></div>
                                <div class="h-3 w-1/2 rounded bg-gray-100 dark:bg-gray-800"></div>
                                <div class="h-3 w-3/4 rounded bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                            <div class="mt-4 h-9 rounded-lg bg-primary-100/70 dark:bg-primary-500/10"></div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div class="h-10 rounded-xl bg-gray-100 dark:bg-gray-800"></div>
                            <div class="h-10 rounded-xl bg-gray-100 dark:bg-gray-800"></div>
                        </div>
                    </div>
                </div>
                <h3 class="mt-6 text-xl font-extrabold text-gray-900 dark:text-white">Transactional Emails.</h3>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Send instant transactional emails via secure API.</p>
                <div class="mt-auto pt-4">
                    <a href="{{ route('features') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-primary-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-primary-300 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 7h16" />
                            <path d="M4 12h16" />
                            <path d="M4 17h16" />
                            <path d="M8 7v10" />
                        </svg>
                        API Emails
                    </a>
                </div>
            </div>

            <div class="group flex flex-col rounded-2xl border border-gray-200 bg-white/70 p-6 shadow-sm backdrop-blur-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900/40">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-b from-gray-50 to-white shadow-sm dark:border-gray-800 dark:from-gray-800/40 dark:to-gray-900">
                    <div class="aspect-[16/10] p-4">
                        <div class="flex items-center justify-between">
                            <div class="h-7 w-28 rounded-md bg-gray-100 dark:bg-gray-800"></div>
                            <div class="h-7 w-12 rounded-md bg-gray-100 dark:bg-gray-800"></div>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="flex items-center justify-between">
                                    <div class="h-3 w-2/3 rounded bg-gray-100 dark:bg-gray-800"></div>
                                    <div class="h-5 w-5 rounded-full bg-emerald-400/20 dark:bg-emerald-500/10"></div>
                                </div>
                                <div class="mt-2 h-3 w-1/2 rounded bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="flex items-center justify-between">
                                    <div class="h-3 w-2/3 rounded bg-gray-100 dark:bg-gray-800"></div>
                                    <div class="h-5 w-5 rounded-full bg-amber-400/20 dark:bg-amber-500/10"></div>
                                </div>
                                <div class="mt-2 h-3 w-1/2 rounded bg-gray-100 dark:bg-gray-800"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <h3 class="mt-6 text-xl font-extrabold text-gray-900 dark:text-white">Domain Management.</h3>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Improve deliverability with custom domain authentication.</p>
                <div class="mt-auto pt-4">
                    <a href="{{ route('features') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-primary-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-primary-300 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V6l-8-4-8 4v6c0 6 8 10 8 10z" />
                        </svg>
                        Domain Control
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<div class="bg-primary-600 dark:bg-primary-700">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
        <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
            <span class="block">Ready to get started?</span>
            <span class="block text-primary-200">Start your free trial today.</span>
        </h2>
        <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
            <div class="inline-flex rounded-md shadow">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-primary-600 bg-white hover:bg-primary-50">
                    Get started
                </a>
            </div>
            <div class="ml-3 inline-flex rounded-md shadow">
                <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-primary-200 bg-primary-700 hover:bg-primary-600 dark:bg-primary-600 dark:hover:bg-primary-500">
                    Learn more
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

