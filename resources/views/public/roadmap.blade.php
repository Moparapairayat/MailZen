@extends('layouts.public')

@section('title', 'Roadmap')

@section('content')
    @php
        $statusStyles = [
            'Released' => [
                'dot' => 'bg-emerald-600 text-white ring-emerald-600/20 dark:ring-emerald-500/20',
                'badge' => 'bg-emerald-600/10 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                'icon' => 'check',
            ],
            'Releasing' => [
                'dot' => 'bg-primary-600 text-white ring-primary-600/20 dark:ring-primary-500/20',
                'badge' => 'bg-primary-600/10 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300',
                'icon' => 'clock',
            ],
            'Planned' => [
                'dot' => 'bg-amber-500 text-white ring-amber-500/20 dark:ring-amber-400/20',
                'badge' => 'bg-amber-500/10 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300',
                'icon' => 'plus',
            ],
        ];

        $items = [
            [
                'date' => '30.12.2005',
                'status' => 'Released',
                'title' => 'Rest API implementation',
                'tags' => [],
            ],
            [
                'date' => '02.01.26',
                'status' => 'Released',
                'title' => 'AI Integration (Gemini, Claude & OpenAI)',
                'tags' => ['AI Dashboard', 'API Management', 'Accessibility control'],
            ],
            [
                'date' => '02.01.26',
                'status' => 'Released',
                'title' => 'Email Template Importer & AI builder',
                'tags' => ['AI Integration', 'AI Email template builder'],
            ],
            [
                'date' => '02.01.26',
                'status' => 'Released',
                'title' => 'RTL support',
                'tags' => ['RTL language support'],
            ],
            [
                'date' => '02.01.26',
                'status' => 'Released',
                'title' => 'SEO Data',
                'tags' => ['Site metadata', 'Favicon', 'meta keywords', 'meta description'],
            ],
            [
                'date' => '06.01.26',
                'status' => 'Released',
                'title' => 'Google sheets',
                'tags' => ['Auto-sync subscribers from Sheets to a list', 'Import contacts with field mapping and tags', 'Export campaigns, metrics, clicks, and activity to Sheets'],
            ],
            [
                'date' => '06.01.26',
                'status' => 'Released',
                'title' => 'Flutterwave payment gateway',
                'tags' => ['Flutterwave', 'live and sandbox'],
            ],
            [
                'date' => '06.01.26',
                'status' => 'Released',
                'title' => 'Google drive',
                'tags' => ['Pick images from Drive for your email templates', 'Save templates to Drive', 'Export backups to a Drive folder'],
            ],
            [
                'date' => '06.01.26',
                'status' => 'Released',
                'title' => 'Zeptomail API integration',
            ],
            [
                'date' => '06.01.26',
                'status' => 'Released',
                'title' => '4 landing pages',
            ],
            [
                'date' => '10.01.26',
                'status' => 'Released',
                'title' => 'Wordpress Integration',
                'tags' => ['Take actions on different wordpress and woocommerce actions', 'WooCommerce', 'Wordpress'],
            ],
            [
                'date' => '11.01.26',
                'status' => 'Released',
                'title' => 'Email Warmup',
                'tags' => ['Email warmup', 'Email deliverability', 'Gradually build sender reputation'],
            ],
            [
                'date' => '11.01.26',
                'status' => 'Released',
                'title' => 'Manual payment method for offline/bank transfer payments.',
                'tags' => ['Manual payment', 'Offline payment', 'Bank transfer payment'],
            ],
            [
                'date' => '11.01.26',
                'status' => 'Released',
                'title' => 'Visual automation builder',
                'tags' => ['webhooks', 'triggers', 'actions'],
            ],
            [
                'date' => '11.01.26',
                'status' => 'Released',
                'title' => 'Email reply tracking',
            ],
            // [
            //     'date' => '15.01.26',
            //     'status' => 'Planned',
            //     'title' => 'Zapier integration',
            //     'tags' => [],
            // ],
            // [
            //     'date' => '15.01.26',
            //     'status' => 'Planned',
            //     'title' => 'Campaigns calender',
            //     'tags' => [],
            // ],
            // [
            //     'date' => '19.01.26',
            //     'status' => 'Planned',
            //     'title' => 'Salesforce integrations',
            //     'tags' => [],
            // ],
            // [
            //     'date' => '19.01.26',
            //     'status' => 'Planned',
            //     'title' => 'Advanced campaign reports',
            //     'tags' => [],
            // ],
            [
                'date' => '31.01.26',
                'status' => 'Planned',
                'title' => 'Suppression lists (global + per list)',
                'tags' => [],
            ],
            [
                'date' => '05.02.26',
                'status' => 'Planned',
                'title' => 'Scheduled email reports',
                'tags' => [],
            ],
            [
                'date' => '10.02.26',
                'status' => 'Planned',
                'title' => 'Bounce & complaint analytics dashboard',
                'tags' => [],
            ],
        ];

        $parseDate = function (string $date): \DateTimeImmutable {
            $parts = explode('.', $date);
            $year = $parts[2] ?? '';
            if (strlen($year) === 2) {
                $year = '20' . $year;
            }
            $normalized = ($parts[0] ?? '01') . '.' . ($parts[1] ?? '01') . '.' . $year;

            return \DateTimeImmutable::createFromFormat('d.m.Y', $normalized) ?: new \DateTimeImmutable('1970-01-01');
        };

        usort($items, function ($a, $b) use ($parseDate) {
            $da = $parseDate($a['date']);
            $db = $parseDate($b['date']);
            if ($da == $db) {
                return strcmp($a['title'], $b['title']);
            }
            return $da <=> $db;
        });
    @endphp

    <div class="bg-white dark:bg-gray-900">
        <section class="relative overflow-hidden bg-white dark:bg-gray-900">
            <div class="absolute inset-0">
                <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-primary-200/40 blur-3xl dark:bg-primary-500/10"></div>
                <div class="absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-indigo-200/40 blur-3xl dark:bg-indigo-500/10"></div>
            </div>

            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl">Product Roadmap</h1>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">Release dates are targets and may shift as we iterate.</p>
                </div>

                <div class="mt-12 mx-auto max-w-3xl">
                    <div class="relative">
                        <div class="absolute left-4 top-0 h-full w-px bg-gray-200 dark:bg-gray-700"></div>

                        <div class="space-y-6">
                            @foreach($items as $item)
                                @php
                                    $style = $statusStyles[$item['status']] ?? $statusStyles['Planned'];
                                @endphp

                                <div class="relative pl-12">
                                    <div class="absolute left-0 top-2 flex h-8 w-8 items-center justify-center rounded-full {{ $style['dot'] }} ring-4">
                                        @if($style['icon'] === 'check')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 6 9 17l-5-5" />
                                            </svg>
                                        @elseif($style['icon'] === 'clock')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 6v6l4 2" />
                                                <path d="M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 2v20" />
                                                <path d="M2 12h20" />
                                            </svg>
                                        @endif
                                    </div>

                                    <div class="rounded-2xl border border-gray-200 bg-white/90 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <time class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $item['date'] }}</time>
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $style['badge'] }}">{{ $item['status'] }}</span>
                                        </div>

                                        <h3 class="mt-3 text-lg font-extrabold text-gray-900 dark:text-white">{{ $item['title'] }}</h3>

                                        @if(!empty($item['tags']))
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                @foreach($item['tags'] as $tag)
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="relative pl-12">
                                <div class="absolute left-0 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-gray-400 text-white ring-4 ring-gray-400/20 dark:bg-gray-600 dark:ring-gray-600/20">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 6v12" />
                                        <path d="M6 12h12" />
                                    </svg>
                                </div>

                                <div class="rounded-2xl border border-gray-200 bg-white/90 p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900/60">
                                    <h3 class="text-lg font-extrabold text-gray-900 dark:text-white">More coming soon</h3>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">We’re actively building new features and integrations. Stay tuned.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 text-center">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">Get Started</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
