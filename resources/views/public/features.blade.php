@extends('layouts.public')

@section('title', 'Features')

@section('content')
@php
    $getText = function (string $key, string $default): string {
        try {
            $val = \App\Models\Setting::get($key, $default);
        } catch (\Throwable $e) {
            $val = $default;
        }

        $val = is_string($val) ? $val : $default;
        $val = trim($val);

        return $val !== '' ? $val : $default;
    };

    $heroTitle = $getText('features_hero_title', 'Powerful Features for Email Marketing');
    $heroSubtitle = $getText('features_hero_subtitle', 'Everything you need to create, send, and track successful email campaigns.');

    $sections = [];
    for ($i = 1; $i <= 4; $i++) {
        $sections[$i] = [
            'title' => $getText('features_section_' . $i . '_title', ''),
            'description' => $getText('features_section_' . $i . '_description', ''),
            'dt' => [
                1 => $getText('features_section_' . $i . '_dt_1', ''),
                2 => $getText('features_section_' . $i . '_dt_2', ''),
                3 => $getText('features_section_' . $i . '_dt_3', ''),
            ],
            'dd' => [
                1 => $getText('features_section_' . $i . '_dd_1', ''),
                2 => $getText('features_section_' . $i . '_dd_2', ''),
                3 => $getText('features_section_' . $i . '_dd_3', ''),
            ],
            'bullets' => [
                1 => $getText('features_section_' . $i . '_bullet_1', ''),
                2 => $getText('features_section_' . $i . '_bullet_2', ''),
                3 => $getText('features_section_' . $i . '_bullet_3', ''),
            ],
        ];
    }

    $sections[1]['title'] = $sections[1]['title'] !== '' ? $sections[1]['title'] : 'Email List Management';
    $sections[1]['description'] = $sections[1]['description'] !== '' ? $sections[1]['description'] : 'Organize and manage your subscribers with powerful list management tools. Keep your lists clean, segmented, and engaged.';
    $sections[1]['dt'][1] = $sections[1]['dt'][1] !== '' ? $sections[1]['dt'][1] : 'Subscriber Management';
    $sections[1]['dd'][1] = $sections[1]['dd'][1] !== '' ? $sections[1]['dd'][1] : 'Import, export, and manage subscribers with ease. Support for custom fields, tags, and segmentation.';
    $sections[1]['dt'][2] = $sections[1]['dt'][2] !== '' ? $sections[1]['dt'][2] : 'Double Opt-in';
    $sections[1]['dd'][2] = $sections[1]['dd'][2] !== '' ? $sections[1]['dd'][2] : 'Ensure list quality with double opt-in confirmation. Automatically verify email addresses and reduce bounces.';
    $sections[1]['dt'][3] = $sections[1]['dt'][3] !== '' ? $sections[1]['dt'][3] : 'List Segmentation';
    $sections[1]['dd'][3] = $sections[1]['dd'][3] !== '' ? $sections[1]['dd'][3] : 'Segment your audience based on behavior, preferences, or custom fields for targeted campaigns.';
    $sections[1]['bullets'][1] = $sections[1]['bullets'][1] !== '' ? $sections[1]['bullets'][1] : 'Unlimited lists';
    $sections[1]['bullets'][2] = $sections[1]['bullets'][2] !== '' ? $sections[1]['bullets'][2] : 'Custom fields & tags';
    $sections[1]['bullets'][3] = $sections[1]['bullets'][3] !== '' ? $sections[1]['bullets'][3] : 'Import/Export CSV';

    $sections[2]['title'] = $sections[2]['title'] !== '' ? $sections[2]['title'] : 'Email Campaigns';
    $sections[2]['description'] = $sections[2]['description'] !== '' ? $sections[2]['description'] : 'Create beautiful, responsive email campaigns that engage your audience and drive results.';
    $sections[2]['dt'][1] = $sections[2]['dt'][1] !== '' ? $sections[2]['dt'][1] : 'Drag & Drop Editor';
    $sections[2]['dd'][1] = $sections[2]['dd'][1] !== '' ? $sections[2]['dd'][1] : 'Build professional emails with our intuitive drag-and-drop editor. No coding required.';
    $sections[2]['dt'][2] = $sections[2]['dt'][2] !== '' ? $sections[2]['dt'][2] : 'Responsive Templates';
    $sections[2]['dd'][2] = $sections[2]['dd'][2] !== '' ? $sections[2]['dd'][2] : 'Choose from beautiful, mobile-responsive templates or create your own custom designs.';
    $sections[2]['dt'][3] = $sections[2]['dt'][3] !== '' ? $sections[2]['dt'][3] : 'Scheduling & Automation';
    $sections[2]['dd'][3] = $sections[2]['dd'][3] !== '' ? $sections[2]['dd'][3] : 'Schedule campaigns for the perfect time or set up automated sequences based on triggers.';
    $sections[2]['bullets'][1] = $sections[2]['bullets'][1] !== '' ? $sections[2]['bullets'][1] : 'Unlimited campaigns';
    $sections[2]['bullets'][2] = $sections[2]['bullets'][2] !== '' ? $sections[2]['bullets'][2] : 'A/B testing';
    $sections[2]['bullets'][3] = $sections[2]['bullets'][3] !== '' ? $sections[2]['bullets'][3] : 'Real-time tracking';

    $sections[3]['title'] = $sections[3]['title'] !== '' ? $sections[3]['title'] : 'Auto Responders';
    $sections[3]['description'] = $sections[3]['description'] !== '' ? $sections[3]['description'] : 'Automate your email marketing with triggered campaigns that engage subscribers at the right time.';
    $sections[3]['dt'][1] = $sections[3]['dt'][1] !== '' ? $sections[3]['dt'][1] : 'Welcome Series';
    $sections[3]['dd'][1] = $sections[3]['dd'][1] !== '' ? $sections[3]['dd'][1] : 'Automatically send welcome emails to new subscribers with customizable sequences.';
    $sections[3]['dt'][2] = $sections[3]['dt'][2] !== '' ? $sections[3]['dt'][2] : 'Triggered Campaigns';
    $sections[3]['dd'][2] = $sections[3]['dd'][2] !== '' ? $sections[3]['dd'][2] : 'Set up campaigns that trigger based on subscriber actions, dates, or field changes.';
    $sections[3]['dt'][3] = $sections[3]['dt'][3] !== '' ? $sections[3]['dt'][3] : 'Drip Campaigns';
    $sections[3]['dd'][3] = $sections[3]['dd'][3] !== '' ? $sections[3]['dd'][3] : 'Create multi-email sequences that nurture leads and guide them through your funnel.';
    $sections[3]['bullets'][1] = $sections[3]['bullets'][1] !== '' ? $sections[3]['bullets'][1] : 'Multiple triggers';
    $sections[3]['bullets'][2] = $sections[3]['bullets'][2] !== '' ? $sections[3]['bullets'][2] : 'Delay scheduling';
    $sections[3]['bullets'][3] = $sections[3]['bullets'][3] !== '' ? $sections[3]['bullets'][3] : 'Unlimited sequences';

    $sections[4]['title'] = $sections[4]['title'] !== '' ? $sections[4]['title'] : 'Analytics & Reporting';
    $sections[4]['description'] = $sections[4]['description'] !== '' ? $sections[4]['description'] : 'Track your campaign performance with detailed analytics and insights.';
    $sections[4]['dt'][1] = $sections[4]['dt'][1] !== '' ? $sections[4]['dt'][1] : 'Real-time Tracking';
    $sections[4]['dd'][1] = $sections[4]['dd'][1] !== '' ? $sections[4]['dd'][1] : 'Monitor opens, clicks, bounces, and unsubscribes in real-time as your campaigns send.';
    $sections[4]['dt'][2] = $sections[4]['dt'][2] !== '' ? $sections[4]['dt'][2] : 'Detailed Reports';
    $sections[4]['dd'][2] = $sections[4]['dd'][2] !== '' ? $sections[4]['dd'][2] : 'Get comprehensive reports on campaign performance, subscriber engagement, and ROI.';
    $sections[4]['dt'][3] = $sections[4]['dt'][3] !== '' ? $sections[4]['dt'][3] : 'Export Data';
    $sections[4]['dd'][3] = $sections[4]['dd'][3] !== '' ? $sections[4]['dd'][3] : 'Export your analytics data to CSV or PDF for further analysis and reporting.';
    $sections[4]['bullets'][1] = $sections[4]['bullets'][1] !== '' ? $sections[4]['bullets'][1] : 'Open & click rates';
    $sections[4]['bullets'][2] = $sections[4]['bullets'][2] !== '' ? $sections[4]['bullets'][2] : 'Bounce tracking';
    $sections[4]['bullets'][3] = $sections[4]['bullets'][3] !== '' ? $sections[4]['bullets'][3] : 'Subscriber insights';
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

        <div class="mt-20 space-y-24">
            <!-- Email Lists Feature -->
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $sections[1]['title'] }}</h2>
                    <p class="mt-3 text-lg text-gray-500 dark:text-gray-400">
                        {{ $sections[1]['description'] }}
                    </p>
                    <dl class="mt-10 space-y-10">
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[1]['dt'][1] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[1]['dd'][1] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[1]['dt'][2] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[1]['dd'][2] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[1]['dt'][3] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[1]['dd'][3] }}
                            </dd>
                        </div>
                    </dl>
                </div>
                <div class="mt-10 -mx-4 relative lg:mt-0">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-8">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[1]['bullets'][1] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[1]['bullets'][2] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[1]['bullets'][3] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campaigns Feature -->
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                <div class="lg:col-start-2">
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $sections[2]['title'] }}</h2>
                    <p class="mt-3 text-lg text-gray-500 dark:text-gray-400">
                        {{ $sections[2]['description'] }}
                    </p>
                    <dl class="mt-10 space-y-10">
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[2]['dt'][1] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[2]['dd'][1] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[2]['dt'][2] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[2]['dd'][2] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[2]['dt'][3] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[2]['dd'][3] }}
                            </dd>
                        </div>
                    </dl>
                </div>
                <div class="mt-10 -mx-4 relative lg:mt-0 lg:col-start-1">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-8">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[2]['bullets'][1] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[2]['bullets'][2] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[2]['bullets'][3] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auto Responders Feature -->
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $sections[3]['title'] }}</h2>
                    <p class="mt-3 text-lg text-gray-500 dark:text-gray-400">
                        {{ $sections[3]['description'] }}
                    </p>
                    <dl class="mt-10 space-y-10">
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[3]['dt'][1] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[3]['dd'][1] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[3]['dt'][2] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[3]['dd'][2] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[3]['dt'][3] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[3]['dd'][3] }}
                            </dd>
                        </div>
                    </dl>
                </div>
                <div class="mt-10 -mx-4 relative lg:mt-0">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-8">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[3]['bullets'][1] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[3]['bullets'][2] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[3]['bullets'][3] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Feature -->
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
                <div class="lg:col-start-2">
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $sections[4]['title'] }}</h2>
                    <p class="mt-3 text-lg text-gray-500 dark:text-gray-400">
                        {{ $sections[4]['description'] }}
                    </p>
                    <dl class="mt-10 space-y-10">
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[4]['dt'][1] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[4]['dd'][1] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[4]['dt'][2] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[4]['dd'][2] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ $sections[4]['dt'][3] }}</dt>
                            <dd class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                {{ $sections[4]['dd'][3] }}
                            </dd>
                        </div>
                    </dl>
                </div>
                <div class="mt-10 -mx-4 relative lg:mt-0 lg:col-start-1">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-8">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[4]['bullets'][1] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[4]['bullets'][2] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $sections[4]['bullets'][3] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

