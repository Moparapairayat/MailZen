@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif
    <!-- Category Tabs -->
    @if($categories && count($categories) > 0)
        <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <nav class="-mb-px flex min-w-max space-x-6 sm:space-x-8 px-2 sm:px-0" aria-label="Tabs">
                @php
                    $tabUpdateAvailable = false;
                    if (is_array($updateStatus ?? null)) {
                        $tabUpdateAvailable = (bool) ($updateStatus['update_available'] ?? false);
                    }
                @endphp
                @foreach($categories as $cat)
                    <a
                        href="{{ route('admin.settings.index', ['category' => $cat]) }}"
                        class="{{ $category === $cat ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                    >
                        <span class="inline-flex items-center gap-2">
                            <span>{{ ucfirst($cat) }}</span>
                            @if($cat === 'updates' && $tabUpdateAvailable)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-500 text-white">{{ __('Update') }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach

                @admincan('admin.translations.access')
                    <a
                        href="{{ route('admin.translations.locales.index') }}"
                        class="{{ request()->routeIs('admin.translations.*') ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                    >
                        Translations
                    </a>
                @endadmincan

                <a
                    href="{{ route('admin.settings.logs') }}"
                    class="{{ request()->routeIs('admin.settings.logs') ? '!border-primary-500 text-primary-600 dark:text-primary-400 border-b-2' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap shrink-0 py-3 sm:py-4 px-2 sm:px-1 font-medium text-sm"
                >
                    Logs
                </a>
            </nav>
        </div>
    @endif

    <!-- Settings Form -->
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('POST')

        <input type="hidden" name="category" value="{{ $category }}">

        <x-card>
            @if($category === 'updates')
                @php
                    $installedVersion = null;
                    $installedVersion = trim((string) config('mailzen.version', ''));
                    if ($installedVersion === '') {
                        $installedVersion = $settings['updates']->get('app_version') ?? null;
                        $installedVersion = is_string($installedVersion) ? trim((string) $installedVersion) : null;
                    }

                    $updateStatusPayload = is_array($updateStatus ?? null) ? $updateStatus : null;
                    $updateAvailableFlag = is_array($updateStatusPayload) ? (bool) ($updateStatusPayload['update_available'] ?? false) : false;
                    $updateStatusLatest = is_array($updateStatusPayload) && is_string($updateStatusPayload['latest_version'] ?? null) ? $updateStatusPayload['latest_version'] : null;
                    $updateStatusCheckedAt = is_array($updateStatusPayload) && is_string($updateStatusPayload['checked_at'] ?? null) ? $updateStatusPayload['checked_at'] : null;

                    $installTargetVersion = is_string($updateStatusLatest) && trim((string) $updateStatusLatest) !== ''
                        ? (string) $updateStatusLatest
                        : null;

                    $latestVersion = null;
                    $productData = null;
                    if (is_array($updateProduct ?? null)) {
                        $payload = $updateProduct['data'] ?? null;
                        if (is_array($payload)) {
                            if (is_array($payload['data'] ?? null)) {
                                $productData = $payload['data'];
                            } elseif (is_array($payload['product'] ?? null)) {
                                $productData = $payload['product'];
                            } else {
                                $productData = $payload;
                            }
                        }
                    }

                    if (is_array($productData)) {
                        $latestVersion = $productData['latest_version']
                            ?? ($productData['latestVersion'] ?? ($productData['latest'] ?? null));

                        if (!is_string($latestVersion) || trim((string) $latestVersion) === '') {
                            $latestVersion = $productData['version'] ?? null;
                        }
                    }

                    if (!is_string($latestVersion) || trim((string) $latestVersion) === '') {
                        $changelogData = is_array($updateChangelogs ?? null) ? ($updateChangelogs['data'] ?? null) : null;
                        if (is_array($changelogData)) {
                            $versionMap = null;
                            if (is_array($changelogData['releases'] ?? null)) {
                                $versionMap = $changelogData['releases'];
                            } elseif (is_array($changelogData['changelog'] ?? null)) {
                                $versionMap = $changelogData['changelog'];
                            }

                            if (is_array($versionMap) && !empty($versionMap)) {
                                $versions = array_keys($versionMap);
                                usort($versions, function ($a, $b) {
                                    return version_compare((string) $b, (string) $a);
                                });
                                $latestVersion = $versions[0] ?? null;
                            }
                        }
                    }

                    if (!$updateAvailableFlag && is_string($latestVersion) && is_string($installedVersion) && trim($latestVersion) !== '' && trim($installedVersion) !== '') {
                        $updateAvailableFlag = version_compare((string) $latestVersion, (string) $installedVersion, '>');
                    }

                    if (!is_string($installTargetVersion) || trim((string) $installTargetVersion) === '') {
                        $installTargetVersion = is_string($latestVersion) && trim((string) $latestVersion) !== ''
                            ? (string) $latestVersion
                            : null;
                    }
                @endphp

                <div class="space-y-6">
                    @if($updateAvailableFlag)
                        <div class="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg dark:bg-blue-900/50 dark:border-blue-800 dark:text-blue-200">
                            <p class="font-semibold">Update available</p>
                            <p class="mt-1 text-sm">Installed: {{ is_string($installedVersion) && trim($installedVersion) !== '' ? $installedVersion : '—' }} | Latest: {{ is_string($updateStatusLatest) && trim($updateStatusLatest) !== '' ? $updateStatusLatest : (is_string($latestVersion) ? $latestVersion : '—') }}</p>
                            @if(is_string($updateStatusCheckedAt) && trim($updateStatusCheckedAt) !== '')
                                <p class="mt-1 text-xs">Last checked: {{ $updateStatusCheckedAt }}</p>
                            @endif
                        </div>
                    @endif
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Updates</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check the latest available version from your update server and generate a download link.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Installed Version</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ is_string($installedVersion) && trim($installedVersion) !== '' ? $installedVersion : '—' }}</p>
                        </div>
                        <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Latest Version</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ is_string($latestVersion) && trim((string) $latestVersion) !== '' ? $latestVersion : '—' }}
                            </p>
                            @if(is_array($updateProduct ?? null) && !($updateProduct['success'] ?? false))
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $updateProduct['message'] ?? 'Unable to fetch update info.' }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Install Update</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Install the latest version in the background. The site will go into maintenance mode during installation.</p>
                            </div>
                            @admincan('admin.settings.edit')
                                <x-button
                                    type="submit"
                                    variant="primary"
                                    size="sm"
                                    formaction="{{ route('admin.settings.install-update') }}"
                                    formmethod="POST"
                                    :disabled="!$updateAvailableFlag"
                                    onclick="return confirm('Are you sure you want to install the update? The site will go into maintenance mode during the update.')"
                                >Install Update</x-button>
                            @endadmincan
                        </div>

                        @if(is_string($installTargetVersion ?? null) && trim((string) $installTargetVersion) !== '')
                            <input type="hidden" name="target_version" value="{{ $installTargetVersion }}">
                        @endif

                        @php
                            $installState = is_array($updateInstallState ?? null) ? $updateInstallState : null;
                            $installInProgress = is_array($installState) ? (bool) ($installState['in_progress'] ?? false) : false;
                            $installStatus = is_array($installState) && is_string($installState['status'] ?? null) ? $installState['status'] : null;
                            $installMessage = is_array($installState) && is_string($installState['message'] ?? null) ? $installState['message'] : null;
                            $installVersion = is_array($installState) && is_string($installState['version'] ?? null) ? $installState['version'] : null;
                        @endphp

                        @if($installInProgress || (is_string($installStatus) && trim((string) $installStatus) !== '') || (is_string($installMessage) && trim((string) $installMessage) !== ''))
                            <div class="mt-4 p-3 rounded-md border border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Installer status</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    {{ is_string($installStatus) ? ucfirst($installStatus) : '—' }}
                                    @if(is_string($installVersion) && trim($installVersion) !== '')
                                        ({{ $installVersion }})
                                    @endif
                                </p>
                                @if(is_string($installMessage) && trim($installMessage) !== '')
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $installMessage }}</p>
                                @endif
                            </div>
                        @endif

                        @if(is_string($updateLastFailureReason ?? null) && trim((string) $updateLastFailureReason) !== '')
                            <div class="mt-4 p-3 rounded-md border border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
                                <p class="text-sm font-semibold">Last update failed</p>
                                <p class="mt-1 text-sm">
                                    @if(is_string($updateLastFailureVersion ?? null) && trim((string) $updateLastFailureVersion) !== '')
                                        Version: {{ $updateLastFailureVersion }}
                                    @else
                                        Version: —
                                    @endif
                                    @if(is_string($updateLastFailureAt ?? null) && trim((string) $updateLastFailureAt) !== '')
                                        | Date: {{ $updateLastFailureAt }}
                                    @endif
                                </p>
                                <p class="mt-1 text-xs">{{ $updateLastFailureReason }}</p>
                            </div>
                        @endif

                        @if(is_string($updateDownloadUrl ?? null) && trim((string) $updateDownloadUrl) !== '')
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Download URL</label>
                                <div class="mt-2 flex flex-col sm:flex-row sm:items-center gap-2">
                                    <input
                                        type="text"
                                        readonly
                                        value="{{ $updateDownloadUrl }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                    <x-button href="{{ $updateDownloadUrl }}" variant="secondary" size="sm" target="_blank" rel="noopener noreferrer">Open</x-button>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This link may expire depending on your update server configuration.</p>
                            </div>
                        @endif
                    </div>

                    <div class="pt-2">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Configuration</h4>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">

                            @php
                                $licenseCheckPayload = is_array($updateLicenseCheck ?? null) ? ($updateLicenseCheck['data'] ?? null) : null;
                                $licenseCheckTopStatus = is_array($licenseCheckPayload) && is_string($licenseCheckPayload['status'] ?? null) ? $licenseCheckPayload['status'] : null;
                                $licenseCheckTopMessage = is_array($licenseCheckPayload) && is_string($licenseCheckPayload['message'] ?? null) ? $licenseCheckPayload['message'] : null;

                                $licenseCheckRequestError = null;
                                if (is_array($updateLicenseCheck ?? null) && !($updateLicenseCheck['success'] ?? false)) {
                                    $licenseCheckRequestError = is_string($updateLicenseCheck['message'] ?? null) ? $updateLicenseCheck['message'] : null;
                                }

                                $licenseObject = null;
                                if (is_array($licenseCheckPayload) && is_array($licenseCheckPayload['data'] ?? null) && is_array($licenseCheckPayload['data']['license'] ?? null)) {
                                    $licenseObject = $licenseCheckPayload['data']['license'];
                                }

                                $savedLicense = null;
                                try {
                                    $savedLicense = \App\Models\Setting::get('update_license_key');
                                } catch (\Throwable $e) {
                                    $savedLicense = null;
                                }

                                $maskedLicense = null;
                                if (is_string($savedLicense) && trim($savedLicense) !== '') {
                                    $clean = trim((string) $savedLicense);
                                    if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $clean)) {
                                        $maskedLicense = substr($clean, 0, 8) . '-XXXX-XXXX-XXXX-' . substr($clean, -12);
                                    } else {
                                        $maskedLicense = substr($clean, 0, 8) . '…' . substr($clean, -6);
                                    }
                                }
                            @endphp

                            <div class="md:col-span-2">
                                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">License</p>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Status and support information from the license server.</p>
                                        </div>
                                        @admincan('admin.settings.edit')
                                            <div class="shrink-0">
                                                <x-button
                                                    type="button"
                                                    variant="danger"
                                                    size="sm"
                                                    onclick="if(confirm('Deactivate this license for the current domain?')) { deactivateLicense(); }"
                                                >Deactivate</x-button>
                                            </div>
                                        @endadmincan
                                    </div>

                                    @if(is_string($licenseCheckRequestError) && trim($licenseCheckRequestError) !== '')
                                        <p class="mt-3 text-sm text-red-600 dark:text-red-400">{{ $licenseCheckRequestError }}</p>
                                    @endif

                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ is_string($licenseObject['status'] ?? null) ? $licenseObject['status'] : ($licenseCheckTopStatus ?: '—') }}</p>
                                            @if(is_string($licenseCheckTopMessage) && trim($licenseCheckTopMessage) !== '')
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $licenseCheckTopMessage }}</p>
                                            @endif
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">License</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $maskedLicense ?: '—' }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Support Expiry</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ is_string($licenseObject['supported_until'] ?? null) ? $licenseObject['supported_until'] : '—' }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">License Type</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ is_string($licenseObject['license_type'] ?? null) ? $licenseObject['license_type'] : '—' }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Purchase Date</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ is_string($licenseObject['purchase_date'] ?? null) ? $licenseObject['purchase_date'] : '—' }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Envato Username</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ is_string($licenseObject['envato_username'] ?? null) ? $licenseObject['envato_username'] : '—' }}</p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Domains</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ is_numeric($licenseObject['active_domains'] ?? null) ? (int) $licenseObject['active_domains'] : '—' }}
                                                /
                                                {{ is_numeric($licenseObject['max_domains'] ?? null) ? (int) $licenseObject['max_domains'] : '—' }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Normalized Domain</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ is_string($licenseObject['normalized_domain'] ?? null) ? $licenseObject['normalized_domain'] : '—' }}</p>
                                        </div>
                                    </div>

                                    @if(is_array($licenseObject) && is_array($licenseObject['domains'] ?? null) && !empty($licenseObject['domains']))
                                        <div class="mt-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Registered Domains</p>
                                            <div class="mt-2 overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                                        <tr>
                                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Domain</th>
                                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                                        @foreach($licenseObject['domains'] as $d => $st)
                                                            <tr>
                                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ is_string($d) ? $d : '' }}</td>
                                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ is_string($st) ? $st : '' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>


                            <div class="md:col-span-2">
                                <label for="setting_update_license_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">License Key</label>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your purchase code / license key and click Activate.</p>
                                <div class="mt-2 flex gap-2">
                                    <input
                                        type="text"
                                        name="update_license_key"
                                        id="setting_update_license_key"
                                        value="{{ old('update_license_key') }}"
                                        autocomplete="new-password"
                                        placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                    <button
                                        type="button"
                                        onclick="activateLicense()"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 whitespace-nowrap"
                                    >
                                        Activate
                                    </button>
                                </div>
                                @error('update_license_key')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <script>
                                function activateLicense() {
                                    var licenseKey = document.getElementById('setting_update_license_key').value;
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route('admin.settings.license-activate') }}';
                                    
                                    var csrf = document.createElement('input');
                                    csrf.type = 'hidden';
                                    csrf.name = '_token';
                                    csrf.value = '{{ csrf_token() }}';
                                    form.appendChild(csrf);
                                    
                                    var input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'update_license_key';
                                    input.value = licenseKey;
                                    form.appendChild(input);
                                    
                                    document.body.appendChild(form);
                                    form.submit();
                                }

                                function deactivateLicense() {
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route('admin.settings.license-deactivate') }}';
                                    
                                    var csrf = document.createElement('input');
                                    csrf.type = 'hidden';
                                    csrf.name = '_token';
                                    csrf.value = '{{ csrf_token() }}';
                                    form.appendChild(csrf);
                                    
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            </script>
                        </div>
                    </div>
                </div>

            @elseif($category === 'changelogs')
                @php
                    $items = [];
                    $changelogMap = [];
                    $releasesMap = [];
                    $sortedVersions = [];
                    if (is_array($updateChangelogs ?? null)) {
                        $data = $updateChangelogs['data'] ?? null;

                        if (is_array($data) && is_array($data['changelog'] ?? null)) {
                            $changelogMap = $data['changelog'];
                            $releasesMap = is_array($data['releases'] ?? null) ? $data['releases'] : [];
                        } elseif (is_array($data) && is_array($data['changelogs'] ?? null)) {
                            $items = $data['changelogs'];
                        } elseif (is_array($data) && is_array($data['data'] ?? null)) {
                            $items = $data['data'];
                        } elseif (is_array($data)) {
                            $items = $data;
                        }
                    }

                    if (is_array($changelogMap) && !empty($changelogMap)) {
                        $sortedVersions = array_keys($changelogMap);
                        usort($sortedVersions, function ($a, $b) {
                            return version_compare((string) $b, (string) $a);
                        });
                    }
                @endphp

                <div class="space-y-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Changelogs</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Release notes fetched from your update server.</p>
                        </div>
                    </div>

                    @if(is_array($updateChangelogs ?? null) && !($updateChangelogs['success'] ?? false))
                        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg dark:bg-red-900/50 dark:border-red-800 dark:text-red-200">
                            {{ $updateChangelogs['message'] ?? 'Unable to fetch changelogs.' }}
                        </div>
                    @endif

                    @if(!empty($sortedVersions))
                        <div class="space-y-4">
                            @foreach($sortedVersions as $version)
                                @php
                                    $release = is_array($releasesMap[$version] ?? null) ? $releasesMap[$version] : [];
                                    $releaseDate = is_string($release['release_date'] ?? null) ? $release['release_date'] : null;

                                    $releaseChangelog = is_array($release['changelog'] ?? null) ? $release['changelog'] : null;
                                    $entry = is_array($releaseChangelog) ? $releaseChangelog : (is_array($changelogMap[$version] ?? null) ? $changelogMap[$version] : []);
                                @endphp

                                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $version }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $releaseDate ?: '' }}</p>
                                    </div>

                                    <div class="mt-3 space-y-3">
                                        @foreach($entry as $type => $messages)
                                            @php
                                                $title = is_string($type) ? ucfirst(str_replace('_', ' ', $type)) : 'Changes';
                                                $lines = [];

                                                $messageList = [];
                                                if (is_string($messages) && trim($messages) !== '') {
                                                    $messageList = [$messages];
                                                } elseif (is_array($messages)) {
                                                    $messageList = $messages;
                                                }

                                                if (!empty($messageList)) {
                                                    foreach ($messageList as $msg) {
                                                        if (!is_string($msg)) {
                                                            continue;
                                                        }

                                                        $parts = array_map('trim', explode(',', $msg));
                                                        foreach ($parts as $part) {
                                                            if ($part !== '') {
                                                                $lines[] = $part;
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp

                                            @if(!empty($lines))
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</p>
                                                    <ul class="mt-2 list-disc pl-5 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                                                        @foreach($lines as $line)
                                                            <li>{{ $line }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif(!empty($items))
                        <div class="space-y-4">
                            @foreach($items as $item)
                                @php
                                    $version = is_array($item) && is_string($item['version'] ?? null) ? $item['version'] : null;
                                    $date = is_array($item) && is_string($item['date'] ?? null) ? $item['date'] : null;
                                    $content = is_array($item) && is_string($item['content'] ?? null) ? $item['content'] : null;
                                @endphp

                                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $version ?: 'Release' }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $date ?: '' }}</p>
                                    </div>
                                    @if($content)
                                        <div class="mt-3 prose prose-sm max-w-none dark:prose-invert">
                                            {!! $content !!}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <p>No changelog entries available.</p>
                        </div>
                    @endif
                </div>

            @elseif($category === 'cron')
                @php
                    $settingsByKey = $settings->get($category, collect())->keyBy('key');
                    $cronRunOutput = session('cron_run_output');
                    $phpPathOptions = [
                        '/usr/bin/php',
                        '/bin/php',
                        '/usr/bin/php7.4',
                        '/usr/bin/php8.0',
                        '/usr/bin/php8.1',
                        '/usr/bin/php8.3',
                        '/usr/lib/php',
                        '/etc/php',
                        '/usr/include/php',
                        '/usr/share/php',
                    ];
                    $detectedPhpBinary = is_string(PHP_BINARY ?? null) ? (string) PHP_BINARY : '';
                    $selectedPhpPath = $detectedPhpBinary !== '' ? $detectedPhpBinary : '/usr/bin/php';
                    if ($detectedPhpBinary !== '' && !in_array($detectedPhpBinary, $phpPathOptions, true)) {
                        array_unshift($phpPathOptions, $detectedPhpBinary);
                    }
                    $isCustomPhpPath = !in_array($selectedPhpPath, $phpPathOptions, true);
                    $artisanPath = base_path('artisan');
                @endphp

                <div class="space-y-6">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Background Job Setup</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select your PHP executable path, then copy and paste these commands into cPanel Cron Jobs.</p>
                    </div>

                    <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Please specify the PHP executable path on your system:</p>
                            <div class="mt-3 space-y-2" id="cron-php-path-options">
                                @foreach($phpPathOptions as $phpPath)
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input
                                            type="radio"
                                            name="cron_php_path_choice"
                                            value="{{ $phpPath }}"
                                            {{ $selectedPhpPath === $phpPath ? 'checked' : '' }}
                                            class="border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                        >
                                        <span>
                                            {{ $phpPath }}
                                            @if($phpPath === $detectedPhpBinary)
                                                <span class="text-xs text-green-600 dark:text-green-400">(Auto-detected)</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input
                                        type="radio"
                                        name="cron_php_path_choice"
                                        value="other"
                                        {{ $isCustomPhpPath ? 'checked' : '' }}
                                        class="border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                    >
                                    <span>Other path</span>
                                </label>
                                <div>
                                    <input
                                        type="text"
                                        id="cron_php_path_custom"
                                        placeholder="/opt/cpanel/ea-php81/root/usr/bin/php"
                                        value="{{ $isCustomPhpPath ? $selectedPhpPath : '' }}"
                                        class="block w-full md:w-2/3 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Insert the following line into your system cron tab. You can adjust the timing if needed.</p>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Scheduler command</label>
                                <div class="mt-2 flex items-center gap-2">
                                    <input
                                        type="text"
                                        id="cron_schedule_command"
                                        readonly
                                        value="* * * * * {{ $selectedPhpPath }} {{ $artisanPath }} schedule:run >> /dev/null 2>&1"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                    <x-button type="button" variant="secondary" size="sm" data-copy-target="cron_schedule_command">Copy</x-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(is_string($cronRunOutput) && trim((string) $cronRunOutput) !== '')
                        <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Last run output</p>
                            <pre class="mt-2 text-xs whitespace-pre-wrap text-gray-700 dark:text-gray-300">{{ $cronRunOutput }}</pre>
                        </div>
                    @endif

                    <script>
                        (function () {
                            var phpPathOptions = document.querySelectorAll('input[name="cron_php_path_choice"]');
                            var customPhpInput = document.getElementById('cron_php_path_custom');
                            var schedulerInput = document.getElementById('cron_schedule_command');
                            var copyButtons = document.querySelectorAll('[data-copy-target]');
                            var artisanPath = @json($artisanPath);

                            function getSelectedPhpPath() {
                                var checked = document.querySelector('input[name="cron_php_path_choice"]:checked');
                                if (!checked) return '/usr/bin/php';
                                if (checked.value === 'other') {
                                    var customVal = customPhpInput ? customPhpInput.value.trim() : '';
                                    return customVal !== '' ? customVal : '/usr/bin/php';
                                }
                                return checked.value;
                            }

                            function updateCommands() {
                                var phpPath = getSelectedPhpPath();
                                if (schedulerInput) {
                                    schedulerInput.value = '* * * * * ' + phpPath + ' ' + artisanPath + ' schedule:run >> /dev/null 2>&1';
                                }
                            }

                            for (var i = 0; i < phpPathOptions.length; i++) {
                                phpPathOptions[i].addEventListener('change', updateCommands);
                            }

                            if (customPhpInput) {
                                customPhpInput.addEventListener('input', function () {
                                    var otherOption = document.querySelector('input[name="cron_php_path_choice"][value="other"]');
                                    if (otherOption) {
                                        otherOption.checked = true;
                                    }
                                    updateCommands();
                                });
                            }

                            for (var j = 0; j < copyButtons.length; j++) {
                                copyButtons[j].addEventListener('click', function (event) {
                                    var button = event.currentTarget;
                                    var targetId = button.getAttribute('data-copy-target');
                                    var targetInput = targetId ? document.getElementById(targetId) : null;
                                    if (!targetInput) return;

                                    targetInput.select();
                                    targetInput.setSelectionRange(0, targetInput.value.length);

                                    var originalLabel = button.textContent;

                                    if (navigator.clipboard && navigator.clipboard.writeText) {
                                        navigator.clipboard.writeText(targetInput.value).then(function () {
                                            button.textContent = 'Copied';
                                            setTimeout(function () {
                                                button.textContent = originalLabel;
                                            }, 1200);
                                        }).catch(function () {
                                            button.textContent = 'Copy failed';
                                            setTimeout(function () {
                                                button.textContent = originalLabel;
                                            }, 1200);
                                        });
                                        return;
                                    }

                                    try {
                                        document.execCommand('copy');
                                        button.textContent = 'Copied';
                                    } catch (e) {
                                        button.textContent = 'Copy failed';
                                    }

                                    setTimeout(function () {
                                        button.textContent = originalLabel;
                                    }, 1200);
                                });
                            }

                            updateCommands();
                        })();
                    </script>
                </div>

            @elseif($category === 'storage')
                @php
                    $settingsByKey = $settings->get($category, collect())->keyBy('key');
                    $getVal = function (string $key, $default = null) {
                        try {
                            return \App\Models\Setting::get($key, $default);
                        } catch (\Throwable $e) {
                            return $default;
                        }
                    };

                    $s3Configured = is_string($getVal('s3_key')) && trim((string) $getVal('s3_key')) !== ''
                        && is_string($getVal('s3_secret')) && trim((string) $getVal('s3_secret')) !== ''
                        && is_string($getVal('s3_region')) && trim((string) $getVal('s3_region')) !== ''
                        && is_string($getVal('s3_bucket')) && trim((string) $getVal('s3_bucket')) !== '';

                    $wasabiConfigured = is_string($getVal('wasabi_key')) && trim((string) $getVal('wasabi_key')) !== ''
                        && is_string($getVal('wasabi_secret')) && trim((string) $getVal('wasabi_secret')) !== ''
                        && is_string($getVal('wasabi_region')) && trim((string) $getVal('wasabi_region')) !== ''
                        && is_string($getVal('wasabi_bucket')) && trim((string) $getVal('wasabi_bucket')) !== ''
                        && is_string($getVal('wasabi_endpoint')) && trim((string) $getVal('wasabi_endpoint')) !== '';

                    $gcsConfigured = is_string($getVal('gcs_project_id')) && trim((string) $getVal('gcs_project_id')) !== ''
                        && is_string($getVal('gcs_bucket')) && trim((string) $getVal('gcs_bucket')) !== ''
                        && is_string($getVal('gcs_key_file')) && trim((string) $getVal('gcs_key_file')) !== '';
                @endphp

                <div class="space-y-8">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Providers</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enable/disable providers. The app will use your default storage from General settings, and fall back to Local if the provider is disabled or not configured.</p>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Provider</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Active</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Configured</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">Local</td>
                                        <td class="px-4 py-3">
                                            <label class="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    name="storage_local_enabled"
                                                    value="1"
                                                    {{ ($settingsByKey['storage_local_enabled']->value ?? false) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                                >
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enabled</span>
                                            </label>
                                            @error('storage_local_enabled')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">Yes</td>
                                    </tr>

                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">Amazon S3</td>
                                        <td class="px-4 py-3">
                                            <label class="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    name="storage_s3_enabled"
                                                    value="1"
                                                    {{ ($settingsByKey['storage_s3_enabled']->value ?? false) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                                >
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enabled</span>
                                            </label>
                                            @error('storage_s3_enabled')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-4 py-3 text-sm {{ $s3Configured ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ $s3Configured ? 'Yes' : 'No' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">Wasabi</td>
                                        <td class="px-4 py-3">
                                            <label class="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    name="storage_wasabi_enabled"
                                                    value="1"
                                                    {{ ($settingsByKey['storage_wasabi_enabled']->value ?? false) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                                >
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enabled</span>
                                            </label>
                                            @error('storage_wasabi_enabled')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-4 py-3 text-sm {{ $wasabiConfigured ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ $wasabiConfigured ? 'Yes' : 'No' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">Google Cloud Storage</td>
                                        <td class="px-4 py-3">
                                            <label class="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    name="storage_gcs_enabled"
                                                    value="1"
                                                    {{ ($settingsByKey['storage_gcs_enabled']->value ?? false) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                                >
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enabled</span>
                                            </label>
                                            @error('storage_gcs_enabled')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-4 py-3 text-sm {{ $gcsConfigured ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ $gcsConfigured ? 'Yes' : 'No' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Common</h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="setting_storage_public_root" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Storage Public Root</label>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Root folder/prefix to store public assets under (optional).</p>
                                <div class="mt-2">
                                    @php
                                        $storagePublicRootOptions = [
                                            '' => 'None',
                                            'public' => 'public',
                                            'uploads' => 'uploads',
                                            'assets' => 'assets',
                                            'media' => 'media',
                                        ];

                                        $storagePublicRootValue = $getVal('storage_public_root', 'public');
                                        $storagePublicRootValue = is_string($storagePublicRootValue) ? trim($storagePublicRootValue) : '';
                                        $storagePublicRootIsCustom = !array_key_exists($storagePublicRootValue, $storagePublicRootOptions);
                                    @endphp
                                    <select
                                        name="storage_public_root"
                                        id="setting_storage_public_root"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                        @if($storagePublicRootIsCustom)
                                            <option value="{{ $storagePublicRootValue }}" selected>
                                                {{ $storagePublicRootValue === '' ? 'None' : $storagePublicRootValue }}
                                            </option>
                                        @endif
                                        @foreach($storagePublicRootOptions as $optValue => $optLabel)
                                            <option value="{{ $optValue }}" {{ (string) $storagePublicRootValue === (string) $optValue ? 'selected' : '' }}>
                                                {{ $optLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('storage_public_root')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="setting_storage_url_prefix" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Storage URL Prefix</label>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Optional prefix added before /storage in public URLs. Example: <span class="font-mono">public</span> results in <span class="font-mono">/public/storage/...</span>.</p>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="storage_url_prefix"
                                        id="setting_storage_url_prefix"
                                        value="{{ $getVal('storage_url_prefix') }}"
                                        placeholder="public"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('storage_url_prefix')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Amazon S3 Settings</h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="setting_s3_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">S3 Key</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="s3_key"
                                        id="setting_s3_key"
                                        value="{{ $getVal('s3_key') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('s3_key')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_s3_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">S3 Secret</label>
                                <div class="mt-2">
                                    <input
                                        type="password"
                                        name="s3_secret"
                                        id="setting_s3_secret"
                                        value=""
                                        autocomplete="new-password"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('s3_secret')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_s3_region" class="block text-sm font-medium text-gray-700 dark:text-gray-300">S3 Region</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="s3_region"
                                        id="setting_s3_region"
                                        value="{{ $getVal('s3_region') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('s3_region')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_s3_bucket" class="block text-sm font-medium text-gray-700 dark:text-gray-300">S3 Bucket</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="s3_bucket"
                                        id="setting_s3_bucket"
                                        value="{{ $getVal('s3_bucket') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('s3_bucket')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_s3_endpoint" class="block text-sm font-medium text-gray-700 dark:text-gray-300">S3 Endpoint</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="s3_endpoint"
                                        id="setting_s3_endpoint"
                                        value="{{ $getVal('s3_endpoint') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('s3_endpoint')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_s3_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">S3 URL</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="s3_url"
                                        id="setting_s3_url"
                                        value="{{ $getVal('s3_url') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('s3_url')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Wasabi Settings</h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="setting_wasabi_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wasabi Key</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="wasabi_key"
                                        id="setting_wasabi_key"
                                        value="{{ $getVal('wasabi_key') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('wasabi_key')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_wasabi_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wasabi Secret</label>
                                <div class="mt-2">
                                    <input
                                        type="password"
                                        name="wasabi_secret"
                                        id="setting_wasabi_secret"
                                        value=""
                                        autocomplete="new-password"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('wasabi_secret')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_wasabi_region" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wasabi Region</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="wasabi_region"
                                        id="setting_wasabi_region"
                                        value="{{ $getVal('wasabi_region', 'us-east-1') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('wasabi_region')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_wasabi_bucket" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wasabi Bucket</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="wasabi_bucket"
                                        id="setting_wasabi_bucket"
                                        value="{{ $getVal('wasabi_bucket') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('wasabi_bucket')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_wasabi_endpoint" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wasabi Endpoint</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="wasabi_endpoint"
                                        id="setting_wasabi_endpoint"
                                        value="{{ $getVal('wasabi_endpoint') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('wasabi_endpoint')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_wasabi_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wasabi URL</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="wasabi_url"
                                        id="setting_wasabi_url"
                                        value="{{ $getVal('wasabi_url') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('wasabi_url')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Google Cloud Storage Settings</h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="setting_gcs_project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">GCS Project ID</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="gcs_project_id"
                                        id="setting_gcs_project_id"
                                        value="{{ $getVal('gcs_project_id') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('gcs_project_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_gcs_bucket" class="block text-sm font-medium text-gray-700 dark:text-gray-300">GCS Bucket</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="gcs_bucket"
                                        id="setting_gcs_bucket"
                                        value="{{ $getVal('gcs_bucket') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('gcs_bucket')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_gcs_key_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">GCS Key File</label>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Absolute path to service account JSON file on the server.</p>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="gcs_key_file"
                                        id="setting_gcs_key_file"
                                        value="{{ $getVal('gcs_key_file') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('gcs_key_file')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_gcs_path_prefix" class="block text-sm font-medium text-gray-700 dark:text-gray-300">GCS Path Prefix</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="gcs_path_prefix"
                                        id="setting_gcs_path_prefix"
                                        value="{{ $getVal('gcs_path_prefix') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('gcs_path_prefix')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="setting_gcs_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">GCS URL</label>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        name="gcs_url"
                                        id="setting_gcs_url"
                                        value="{{ $getVal('gcs_url') }}"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    >
                                </div>
                                @error('gcs_url')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($category === 'templates')
                @php
                    try {
                        $activeVariant = \App\Models\Setting::get('home_page_variant', '1');
                    } catch (\Throwable $e) {
                        $activeVariant = '1';
                    }
                    $activeVariant = is_string($activeVariant) ? trim($activeVariant) : '1';

                    try {
                        $activeTemplateId = \App\Models\Setting::get('active_public_template', '');
                    } catch (\Throwable $e) {
                        $activeTemplateId = '';
                    }
                    $activeTemplateId = is_string($activeTemplateId) ? trim($activeTemplateId) : '';

                    try {
                        $externalApiBaseUrl = \App\Models\Setting::get('external_templates_api_base_url', '');
                    } catch (\Throwable $e) {
                        $externalApiBaseUrl = '';
                    }
                    $externalApiBaseUrl = is_string($externalApiBaseUrl) ? trim($externalApiBaseUrl) : '';

                    try {
                        $externalApiProductId = \App\Models\Setting::get('external_templates_api_product_id', '');
                    } catch (\Throwable $e) {
                        $externalApiProductId = '';
                    }
                    $externalApiProductId = is_string($externalApiProductId) ? trim($externalApiProductId) : '';

                    try {
                        $externalApiResourceType = \App\Models\Setting::get('external_templates_api_resource_type', 'Post');
                    } catch (\Throwable $e) {
                        $externalApiResourceType = 'Post';
                    }
                    $externalApiResourceType = is_string($externalApiResourceType) ? trim($externalApiResourceType) : 'Post';

                    try {
                        $externalLicenseKey = \App\Models\Setting::get('external_templates_license_key', '');
                    } catch (\Throwable $e) {
                        $externalLicenseKey = '';
                    }
                    $externalLicenseKey = is_string($externalLicenseKey) ? trim($externalLicenseKey) : '';

                    try {
                        $externalLicenseActive = (bool) \App\Models\Setting::get('external_templates_license_active', 0);
                    } catch (\Throwable $e) {
                        $externalLicenseActive = false;
                    }

                    try {
                        $externalTemplates = \App\Models\ExternalTemplate::query()
                            ->orderByDesc('external_updated_at')
                            ->orderByDesc('updated_at')
                            ->limit(60)
                            ->get();
                    } catch (\Throwable $e) {
                        $externalTemplates = collect();
                    }

                    $templates = [
                        [
                            'id' => 'home-5',
                            'name' => 'Modern Landing',
                            'variant' => '5',
                            'description' => 'Modern, conversion-focused homepage with editable text and branding.',
                            'is_pro' => false,
                        ],
                        [
                            'id' => 'home-1-pro',
                            'name' => 'Classic SaaS',
                            'variant' => '1',
                            'description' => 'A classic SaaS layout (Pro).',
                            'is_pro' => true,
                        ],
                        [
                            'id' => 'home-2-pro',
                            'name' => 'Minimal Startup',
                            'variant' => '2',
                            'description' => 'A clean, minimal layout (Pro).',
                            'is_pro' => true,
                        ],
                    ];
                @endphp

                <div class="space-y-6">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Templates</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose a homepage template, then customize its text, colors, and icons with a live preview.</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">External Templates API</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sync templates from your WordPress endpoint and store their JSON payload locally.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $externalLicenseActive ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                    {{ $externalLicenseActive ? 'License Active' : 'License Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Base URL</label>
                                <div class="mt-2">
                                    <input type="text" name="external_templates_api_base_url" value="{{ old('external_templates_api_base_url', $externalApiBaseUrl) }}" class="block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-900 dark:text-gray-100 sm:text-sm" placeholder="https://example.com">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product ID</label>
                                <div class="mt-2">
                                    <input type="text" name="external_templates_api_product_id" value="{{ old('external_templates_api_product_id', $externalApiProductId) }}" class="block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-900 dark:text-gray-100 sm:text-sm" placeholder="123">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resource Type</label>
                                <div class="mt-2">
                                    <input type="text" name="external_templates_api_resource_type" value="{{ old('external_templates_api_resource_type', $externalApiResourceType) }}" class="block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-900 dark:text-gray-100 sm:text-sm" placeholder="Post">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">License Key</label>
                                <div class="mt-2">
                                    <input type="text" name="license_key" value="{{ old('license_key', $externalLicenseKey) }}" class="block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-900 dark:text-gray-100 sm:text-sm" placeholder="Enter your license key">
                                </div>
                            </div>
                            <div class="flex items-end gap-2">
                                <x-button type="submit" variant="secondary" size="sm" formaction="{{ route('admin.settings.templates.external.sync') }}" formmethod="POST">Sync</x-button>
                                <x-button type="submit" variant="secondary" size="sm" formaction="{{ route('admin.settings.templates.external-license.activate') }}" formmethod="POST">Activate License</x-button>
                                <x-button type="submit" variant="secondary" size="sm" formaction="{{ route('admin.settings.templates.external-license.deactivate') }}" formmethod="POST">Deactivate</x-button>
                            </div>
                        </div>
                    </div>

                    @if($externalTemplates->count() > 0)
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">External templates</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Synced from your API. Click “Fetch JSON” to store the template payload locally.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                                @foreach($externalTemplates as $ext)
                                    @php
                                        $extRequiresLicense = (bool) ($ext->requires_license ?? false);
                                        $extLocked = $extRequiresLicense && !$externalLicenseActive;
                                        $extHasJson = !empty($ext->json_fetched_at) && is_string($ext->json_code ?? null) && trim((string) ($ext->json_code ?? '')) !== '';
                                    @endphp

                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                                        <div class="relative aspect-[16/9] bg-gray-100 dark:bg-gray-900">
                                            @if(is_string($ext->preview_image ?? null) && trim((string) $ext->preview_image) !== '')
                                                <img src="{{ $ext->preview_image }}" alt="{{ $ext->name }}" class="absolute inset-0 h-full w-full object-cover">
                                            @endif
                                            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/50 to-transparent"></div>

                                            <div class="absolute left-3 top-3 flex items-center gap-2">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $extLocked ? 'bg-yellow-500 text-white' : 'bg-emerald-600 text-white' }}">
                                                    {{ $extRequiresLicense ? 'Pro' : 'Free' }}
                                                </span>
                                                @if($extHasJson)
                                                    <span class="inline-flex items-center rounded-full bg-primary-600 px-2.5 py-1 text-xs font-semibold text-white">JSON Saved</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="p-4 space-y-3">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $ext->name }}</p>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">#{{ (int) $ext->external_id }} · {{ $ext->builder ?? 'builder' }} · {{ $ext->plan ?? 'plan' }}</p>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2">
                                                @if(is_string($ext->preview_url ?? null) && trim((string) $ext->preview_url) !== '')
                                                    <a href="{{ $ext->preview_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Preview</a>
                                                @endif

                                                @if($extLocked)
                                                    <x-button type="button" variant="secondary" size="sm" disabled>Locked</x-button>
                                                @else
                                                    <x-button type="submit" variant="secondary" size="sm" formaction="{{ route('admin.settings.templates.external.fetch-json', ['externalId' => (int) $ext->external_id]) }}" formmethod="POST">Fetch JSON</x-button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($templates as $template)
                            @php
                                $isPro = (bool) ($template['is_pro'] ?? false);
                                $templateId = (string) ($template['id'] ?? '');
                                $isActive = $activeTemplateId !== ''
                                    ? (string) $activeTemplateId === (string) $templateId
                                    : (string) ($template['variant'] ?? '') === (string) $activeVariant;
                            @endphp

                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                                <div class="relative aspect-[16/9] bg-gradient-to-br from-gray-900 via-gray-700 to-gray-500">
                                    <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/50 to-transparent"></div>

                                    <div class="absolute left-3 top-3 flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $isPro ? 'bg-yellow-500 text-white' : 'bg-emerald-600 text-white' }}">
                                            {{ $isPro ? 'Pro' : 'Free' }}
                                        </span>
                                        @if($isActive)
                                            <span class="inline-flex items-center rounded-full bg-primary-600 px-2.5 py-1 text-xs font-semibold text-white">Active</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-4 space-y-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $template['name'] ?? '' }}</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $template['description'] ?? '' }}</p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <a
                                            href="{{ route('admin.settings.templates.preview', ['template' => $templateId]) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >Preview</a>

                                        @if($isPro)
                                            <x-button type="button" variant="secondary" size="sm" disabled>Locked</x-button>
                                        @else
                                            <x-button
                                                type="submit"
                                                variant="secondary"
                                                size="sm"
                                                formaction="{{ route('admin.settings.templates.activate', ['template' => $templateId]) }}"
                                                formmethod="POST"
                                            >Activate</x-button>

                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700"
                                                data-template-customize="1"
                                                data-template-id="{{ $templateId }}"
                                            >Customize</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="template-customizer" class="fixed inset-0 z-[70] hidden" aria-hidden="true">
                        <div class="absolute inset-0 bg-black/60" data-template-customizer-close></div>
                        <div class="absolute inset-0 flex items-center justify-center p-4">
                            <div class="relative flex h-[92vh] w-full max-w-7xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                                <div class="flex w-full flex-col xl:flex-row">
                                    <div class="flex-1 border-b border-gray-200 dark:border-gray-800 xl:border-b-0 xl:border-r">
                                        <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100" data-template-customizer-title>Customize</p>
                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Live preview updates as you type.</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <a data-template-customizer-open-preview target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">Open</a>
                                                <button type="button" class="inline-flex items-center rounded-md border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" data-template-customizer-close>Close</button>
                                            </div>
                                        </div>
                                        <iframe data-template-customizer-iframe class="h-[calc(92vh-52px)] w-full bg-white dark:bg-gray-900" src="about:blank"></iframe>
                                    </div>

                                    <div class="w-full xl:w-[460px]">
                                        <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Customizations</p>
                                            <div class="flex items-center gap-2">
                                                <button type="button" class="inline-flex items-center rounded-md border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" data-template-customizer-activate>Activate</button>
                                                <button type="button" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700" data-template-customizer-save>Save</button>
                                            </div>
                                        </div>

                                        <div class="h-[calc(92vh-52px)] overflow-y-auto px-4 py-4">
                                            <div class="hidden rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-900/30 dark:text-green-200" data-template-customizer-success></div>
                                            <div class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-900/30 dark:text-red-200" data-template-customizer-error></div>

                                            <div class="space-y-5" data-template-customizer-form>
                                                <div data-template-field="brand_color">
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand Color</label>
                                                    <div class="mt-2 flex items-center gap-3">
                                                        <input type="color" name="brand_color" class="h-10 w-14 rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800">
                                                        <input type="text" name="brand_color_text" readonly class="block w-32 rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                                                    </div>
                                                </div>

                                                <div data-template-field="hero_title">
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hero Title</label>
                                                    <div class="mt-2">
                                                        <input type="text" name="hero_title" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                                                    </div>
                                                </div>

                                                <div data-template-field="hero_subtitle">
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hero Subtitle</label>
                                                    <div class="mt-2">
                                                        <textarea name="hero_subtitle" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:text-sm"></textarea>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 gap-5">
                                                    <div data-template-field="cta_text">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary CTA</label>
                                                        <div class="mt-2">
                                                            <input type="text" name="cta_text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                                                        </div>
                                                    </div>
                                                    <div data-template-field="cta_secondary_text">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secondary CTA</label>
                                                        <div class="mt-2">
                                                            <input type="text" name="cta_secondary_text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 gap-5">
                                                    <div data-template-field="stat_emails_sent">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stat: Emails Sent</label>
                                                        <div class="mt-2">
                                                            <input type="text" name="stat_emails_sent" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                                                        </div>
                                                    </div>
                                                    <div data-template-field="stat_users">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stat: Users</label>
                                                        <div class="mt-2">
                                                            <input type="text" name="stat_users" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                                                        </div>
                                                    </div>
                                                    <div data-template-field="stat_uptime">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stat: Uptime</label>
                                                        <div class="mt-2">
                                                            <input type="text" name="stat_uptime" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Integration Icons</p>
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lucide icon names (e.g. zap, message-square, globe).</p>

                                                    <div class="mt-4 grid grid-cols-1 gap-5">
                                                        @for($i = 1; $i <= 6; $i++)
                                                            @php $k = 'integration_' . $i . '_icon'; @endphp
                                                            <div data-template-field="{{ $k }}">
                                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Integration {{ $i }} Icon</label>
                                                                <div class="mt-2">
                                                                    <input type="text" name="{{ $k }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 sm:text-sm">
                                                                </div>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        (function () {
                            var modal = document.getElementById('template-customizer');
                            if (!modal) return;

                            var csrf = document.querySelector('meta[name="csrf-token"]');
                            var csrfToken = csrf ? csrf.getAttribute('content') : '';

                            var closeEls = modal.querySelectorAll('[data-template-customizer-close]');
                            var iframe = modal.querySelector('[data-template-customizer-iframe]');
                            var titleEl = modal.querySelector('[data-template-customizer-title]');
                            var openPreviewEl = modal.querySelector('[data-template-customizer-open-preview]');
                            var formRoot = modal.querySelector('[data-template-customizer-form]');
                            var saveBtn = modal.querySelector('[data-template-customizer-save]');
                            var activateBtn = modal.querySelector('[data-template-customizer-activate]');
                            var okBox = modal.querySelector('[data-template-customizer-success]');
                            var errBox = modal.querySelector('[data-template-customizer-error]');

                            var currentTemplateId = null;
                            var previewBase = null;

                            var hideMessages = function () {
                                if (okBox) okBox.classList.add('hidden');
                                if (errBox) errBox.classList.add('hidden');
                            };

                            var showError = function (msg) {
                                if (!errBox) return;
                                errBox.textContent = msg;
                                errBox.classList.remove('hidden');
                            };

                            var showSuccess = function (msg) {
                                if (!okBox) return;
                                okBox.textContent = msg;
                                okBox.classList.remove('hidden');
                            };

                            var setFieldVisible = function (key, visible) {
                                var el = modal.querySelector('[data-template-field="' + key + '"]');
                                if (!el) return;
                                if (visible) {
                                    el.classList.remove('hidden');
                                } else {
                                    el.classList.add('hidden');
                                }
                            };

                            var setValue = function (key, val) {
                                var input = modal.querySelector('[name="' + key + '"]');
                                if (!input) return;
                                input.value = (val === null || typeof val === 'undefined') ? '' : String(val);
                            };

                            var updateBrandText = function () {
                                var colorInput = modal.querySelector('[name="brand_color"]');
                                var textInput = modal.querySelector('[name="brand_color_text"]');
                                if (!colorInput || !textInput) return;
                                textInput.value = colorInput.value;
                            };

                            var buildPreviewUrl = function () {
                                if (!previewBase) return null;
                                var params = new URLSearchParams();

                                var inputs = modal.querySelectorAll('[data-template-customizer-form] [name]');
                                for (var i = 0; i < inputs.length; i++) {
                                    var el = inputs[i];
                                    var name = el.getAttribute('name');
                                    if (!name || name === 'brand_color_text') continue;
                                    params.append(name, el.value);
                                }

                                return previewBase + '?' + params.toString();
                            };

                            var refreshPreview = function () {
                                var url = buildPreviewUrl();
                                if (!url) return;
                                if (iframe) iframe.setAttribute('src', url);
                                if (openPreviewEl) openPreviewEl.setAttribute('href', url);
                            };

                            var openModal = function (templateId) {
                                currentTemplateId = templateId;
                                hideMessages();

                                previewBase = @json(route('admin.settings.templates.preview', ['template' => '__TPL__'])).replace('__TPL__', encodeURIComponent(templateId));
                                var valuesUrl = @json(route('admin.settings.templates.values', ['template' => '__TPL__'])).replace('__TPL__', encodeURIComponent(templateId));

                                if (titleEl) titleEl.textContent = 'Customize: ' + templateId;
                                if (iframe) iframe.setAttribute('src', previewBase);
                                if (openPreviewEl) openPreviewEl.setAttribute('href', previewBase);

                                modal.classList.remove('hidden');
                                modal.setAttribute('aria-hidden', 'false');
                                document.body.classList.add('overflow-hidden');

                                fetch(valuesUrl, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                }).then(function (r) {
                                    if (!r.ok) throw new Error('Failed to load template values');
                                    return r.json();
                                }).then(function (payload) {
                                    var keys = Array.isArray(payload.editable_keys) ? payload.editable_keys : [];
                                    var values = payload.values && typeof payload.values === 'object' ? payload.values : {};

                                    if (payload.template && payload.template.name && titleEl) {
                                        titleEl.textContent = 'Customize: ' + payload.template.name;
                                    }

                                    var allWrappers = modal.querySelectorAll('[data-template-customizer-form] [data-template-field]');
                                    for (var i = 0; i < allWrappers.length; i++) {
                                        allWrappers[i].classList.remove('hidden');
                                    }

                                    if (keys.length) {
                                        for (var i2 = 0; i2 < allWrappers.length; i2++) {
                                            var w = allWrappers[i2];
                                            var fieldKey = w.getAttribute('data-template-field');
                                            if (fieldKey && fieldKey !== 'brand_color' && keys.indexOf(fieldKey) === -1) {
                                                w.classList.add('hidden');
                                            }
                                        }
                                    }

                                    Object.keys(values).forEach(function (k) {
                                        setValue(k, values[k]);
                                    });

                                    var bc = modal.querySelector('[name="brand_color"]');
                                    if (bc && values.brand_color) {
                                        bc.value = String(values.brand_color);
                                    }
                                    updateBrandText();
                                    refreshPreview();
                                }).catch(function (e) {
                                    showError(e && e.message ? e.message : 'Failed to load template');
                                });
                            };

                            var closeModal = function () {
                                modal.classList.add('hidden');
                                modal.setAttribute('aria-hidden', 'true');
                                document.body.classList.remove('overflow-hidden');
                                currentTemplateId = null;
                                previewBase = null;
                                if (iframe) iframe.setAttribute('src', 'about:blank');
                            };

                            for (var i = 0; i < closeEls.length; i++) {
                                closeEls[i].addEventListener('click', function () {
                                    closeModal();
                                });
                            }

                            document.addEventListener('keydown', function (e) {
                                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                                    closeModal();
                                }
                            });

                            if (formRoot) {
                                formRoot.addEventListener('input', function (e) {
                                    if (e && e.target && e.target.getAttribute && e.target.getAttribute('name') === 'brand_color') {
                                        updateBrandText();
                                    }
                                    refreshPreview();
                                });

                                formRoot.addEventListener('change', function () {
                                    refreshPreview();
                                });
                            }

                            if (saveBtn) {
                                saveBtn.addEventListener('click', function () {
                                    if (!currentTemplateId) return;
                                    hideMessages();

                                    var url = @json(route('admin.settings.templates.update', ['template' => '__TPL__'])).replace('__TPL__', encodeURIComponent(currentTemplateId));
                                    var data = {};
                                    var inputs = modal.querySelectorAll('[data-template-customizer-form] [name]');
                                    for (var i = 0; i < inputs.length; i++) {
                                        var el = inputs[i];
                                        var name = el.getAttribute('name');
                                        if (!name || name === 'brand_color_text') continue;
                                        data[name] = el.value;
                                    }

                                    fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken
                                        },
                                        body: JSON.stringify(data)
                                    }).then(function (r) {
                                        if (!r.ok) {
                                            return r.json().then(function (payload) {
                                                var msg = payload && payload.message ? payload.message : 'Failed to save';
                                                throw new Error(msg);
                                            }).catch(function () {
                                                throw new Error('Failed to save');
                                            });
                                        }
                                        return r.json();
                                    }).then(function () {
                                        showSuccess('Saved.');
                                    }).catch(function (e) {
                                        showError(e && e.message ? e.message : 'Failed to save');
                                    });
                                });
                            }

                            if (activateBtn) {
                                activateBtn.addEventListener('click', function () {
                                    if (!currentTemplateId) return;
                                    hideMessages();

                                    var url = @json(route('admin.settings.templates.activate', ['template' => '__TPL__'])).replace('__TPL__', encodeURIComponent(currentTemplateId));
                                    fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken
                                        }
                                    }).then(function (r) {
                                        if (!r.ok) throw new Error('Failed to activate');
                                        showSuccess('Activated.');
                                        window.location.reload();
                                    }).catch(function (e) {
                                        showError(e && e.message ? e.message : 'Failed to activate');
                                    });
                                });
                            }

                            var customizeButtons = document.querySelectorAll('[data-template-customize="1"]');
                            for (var j = 0; j < customizeButtons.length; j++) {
                                customizeButtons[j].addEventListener('click', function (e) {
                                    var tpl = e && e.currentTarget ? e.currentTarget.getAttribute('data-template-id') : null;
                                    if (!tpl) return;
                                    openModal(tpl);
                                });
                            }
                        })();
                    </script>
                </div>

            @elseif($category === 'ai' && $settings->has($category) && $settings[$category]->count() > 0)
                @php
                    $aiSettings = $settings[$category];
                    $openaiSettings = $aiSettings->filter(fn ($s) => \Illuminate\Support\Str::startsWith((string) $s->key, 'openai_'));
                    $geminiSettings = $aiSettings->filter(fn ($s) => \Illuminate\Support\Str::startsWith((string) $s->key, 'gemini_'));
                    $claudeSettings = $aiSettings->filter(fn ($s) => \Illuminate\Support\Str::startsWith((string) $s->key, 'claude_'));

                    $otherAiSettings = $aiSettings->reject(function ($s) {
                        return \Illuminate\Support\Str::startsWith((string) $s->key, 'openai_')
                            || \Illuminate\Support\Str::startsWith((string) $s->key, 'gemini_')
                            || \Illuminate\Support\Str::startsWith((string) $s->key, 'claude_');
                    });

                    $groups = [
                        ['title' => 'OpenAI / GPT', 'items' => $openaiSettings],
                        ['title' => 'Gemini', 'items' => $geminiSettings],
                        ['title' => 'Claude', 'items' => $claudeSettings],
                    ];
                @endphp

                <div class="space-y-4">
                    @foreach($groups as $group)
                        @php
                            $items = $group['items'] ?? collect();
                        @endphp

                        @if($items->count() > 0)
                            <details class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800" open>
                                <summary class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $group['title'] ?? '' }}
                                </summary>
                                <div class="px-4 pb-4 pt-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @foreach($items as $setting)
                                        @include('admin.settings._setting_field', ['setting' => $setting])
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    @endforeach

                    @if($otherAiSettings->count() > 0)
                        <details class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                            <summary class="cursor-pointer select-none px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Other') }}
                            </summary>
                            <div class="px-4 pb-4 pt-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($otherAiSettings as $setting)
                                    @include('admin.settings._setting_field', ['setting' => $setting])
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>

            @elseif($category === 'navigation' && $settings->has($category) && $settings[$category]->count() > 0)
                @php
                    $navigationSettings = $settings[$category]
                        ->reject(fn ($s) => in_array((string) ($s->key ?? ''), ['nav_show_home_dropdown', 'nav_show_home_1', 'nav_show_home_2', 'nav_show_home_3', 'nav_show_home_4'], true));
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($navigationSettings as $setting)
                        @include('admin.settings._setting_field', ['setting' => $setting])
                    @endforeach
                </div>

            @elseif($settings->has($category) && $settings[$category]->count() > 0)
                <div class="space-y-6">
                    @foreach($settings[$category] as $setting)
                        @include('admin.settings._setting_field', ['setting' => $setting])
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <p>No settings found for this category.</p>
                    <p class="mt-2 text-sm">Settings will appear here once they are created.</p>
                </div>
            @endif

            <!-- Form Actions -->
            @if($settings->has($category) && $settings[$category]->count() > 0)
                <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                    @admincan('admin.settings.edit')
                        <x-button type="submit" variant="primary">Save Settings</x-button>
                    @endadmincan
                </div>
            @endif
        </x-card>
    </form>
</div>
@endsection

