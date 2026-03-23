<div>
    @php
        $settingLabel = match ((string) $setting->key) {
            'brand_color' => 'Brand Color',
            'home_page_variant' => 'Home Page',
            'home_redirect_enabled' => 'Home Redirect',
            'storage_url_prefix' => 'Storage URL Prefix',
            'toast_position' => 'Toast Position',
            'public_meta_image' => 'Meta / OG Image',
            default => ucwords(str_replace(['_', '-'], ' ', $setting->key)),
        };
    @endphp
    <label for="setting_{{ $setting->key }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $settingLabel }}
    </label>

    @if($setting->description)
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $setting->description }}@if($setting->key === 'storage_url_prefix') Example: set to <span class="font-mono">public</span> to make URLs <span class="font-mono">/public/storage/...</span>.@endif</p>
    @endif

    @if(in_array($setting->key, ['google_client_id', 'google_client_secret'], true))
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            <a
                href="https://console.cloud.google.com/apis/credentials"
                target="_blank"
                rel="noopener noreferrer"
                class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 underline"
            >
                Get your Google OAuth Client ID/Secret (Google Cloud Console → APIs & Services → Credentials)
            </a>
        </p>
    @endif

    @if($setting->key === 'home_redirect_url')
        @php
            // Rendered inside the home_redirect_enabled control.
        @endphp
    @else
    <div class="mt-2">
        @if($setting->key === 'brand_color')
            @php
                $brandColorValue = is_string($setting->value) ? trim($setting->value) : '';
                if ($brandColorValue === '' || !preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $brandColorValue)) {
                    $brandColorValue = '#3b82f6';
                }
            @endphp
            <div class="flex items-center gap-3">
                <input
                    type="color"
                    name="{{ $setting->key }}"
                    id="setting_{{ $setting->key }}"
                    value="{{ $brandColorValue }}"
                    class="h-10 w-14 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                >
                <input
                    type="text"
                    value="{{ $brandColorValue }}"
                    readonly
                    class="block w-32 rounded-md border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                >
            </div>
        @elseif($setting->key === 'home_page_variant')
            @php
                $homeVariantValue = is_string($setting->value) ? trim($setting->value) : '';
                $homeVariantValue = $homeVariantValue !== '' ? $homeVariantValue : '1';
                $homeVariants = [
                    'all' => 'Show all',
                    '1' => 'Home 1',
                    '2' => 'Home 2',
                    '3' => 'Home 3',
                    '4' => 'Home 4',
                    '5' => 'Home 5',
                ];
            @endphp
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                @foreach($homeVariants as $variantValue => $variantLabel)
                    <option value="{{ $variantValue }}" {{ (string) $homeVariantValue === (string) $variantValue ? 'selected' : '' }}>
                        {{ $variantLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($setting->key === 'home_redirect_enabled')
            @php
                $homeRedirectEnabledValue = (bool) ($setting->value ?? false);
                try {
                    $homeRedirectUrlValue = (string) \App\Models\Setting::get('home_redirect_url', '');
                } catch (\Throwable $e) {
                    $homeRedirectUrlValue = '';
                }
            @endphp
            <div x-data="{ enabled: {{ $homeRedirectEnabledValue ? 'true' : 'false' }} }" class="space-y-4">
                <div class="flex items-center">
                    <input type="hidden" name="{{ $setting->key }}" value="0">
                    <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            name="{{ $setting->key }}"
                            id="setting_{{ $setting->key }}"
                            value="1"
                            {{ $homeRedirectEnabledValue ? 'checked' : '' }}
                            @change="enabled = $event.target.checked"
                            class="sr-only peer"
                        >
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>

                <div x-show="enabled" x-cloak>
                    <label for="setting_home_redirect_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Redirect URL</label>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use a full URL (https://...) or a path starting with /</p>
                    <div class="mt-2">
                        <input
                            type="text"
                            name="home_redirect_url"
                            id="setting_home_redirect_url"
                            value="{{ old('home_redirect_url', $homeRedirectUrlValue) }}"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                    </div>
                    @error('home_redirect_url')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @elseif($setting->key === 'toast_position')
            @php
                $toastPositionValue = is_string($setting->value) ? trim($setting->value) : '';
                $toastPositionValue = $toastPositionValue !== '' ? $toastPositionValue : 'top_right';
                $toastPositions = [
                    'top_left' => 'Top Left',
                    'top_center' => 'Top Center',
                    'top_right' => 'Top Right',
                    'bottom_left' => 'Bottom Left',
                    'bottom_center' => 'Bottom Center',
                    'bottom_right' => 'Bottom Right',
                ];
            @endphp
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                @foreach($toastPositions as $posValue => $posLabel)
                    <option value="{{ $posValue }}" {{ (string) old($setting->key, $toastPositionValue) === (string) $posValue ? 'selected' : '' }}>
                        {{ $posLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($setting->key === 'app_logo')
            @if($setting->value)
                <div class="mb-3">
                    <img
                        src="{{ (string) config('filesystems.branding_disk', 'public') === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($setting->value, '/')) : \Illuminate\Support\Facades\Storage::disk((string) config('filesystems.branding_disk', 'public'))->url($setting->value) }}"
                        alt="App Logo"
                        class="h-12 w-auto rounded"
                    />
                </div>
            @endif
            <input
                type="file"
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                accept="image/*"
                class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-100 dark:hover:file:bg-gray-600"
            >
        @elseif($setting->key === 'app_logo_dark')
            @if($setting->value)
                <div class="mb-3">
                    <img
                        src="{{ (string) config('filesystems.branding_disk', 'public') === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($setting->value, '/')) : \Illuminate\Support\Facades\Storage::disk((string) config('filesystems.branding_disk', 'public'))->url($setting->value) }}"
                        alt="App Logo Dark"
                        class="h-12 w-auto rounded"
                    />
                </div>
            @endif
            <input
                type="file"
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                accept="image/*"
                class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-100 dark:hover:file:bg-gray-600"
            >
        @elseif($setting->key === 'site_favicon')
            @if($setting->value)
                <div class="mb-3">
                    <img
                        src="{{ (string) config('filesystems.branding_disk', 'public') === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($setting->value, '/')) : \Illuminate\Support\Facades\Storage::disk((string) config('filesystems.branding_disk', 'public'))->url($setting->value) }}"
                        alt="Site Favicon"
                        class="h-12 w-12 rounded"
                    />
                </div>
            @endif
            <input
                type="file"
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                accept="image/*,.ico"
                class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-100 dark:hover:file:bg-gray-600"
            >
        @elseif($setting->key === 'public_meta_image')
            @if($setting->value)
                <div class="mb-3">
                    <img
                        src="{{ (string) config('filesystems.branding_disk', 'public') === 'public' ? \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($setting->value, '/')) : \Illuminate\Support\Facades\Storage::disk((string) config('filesystems.branding_disk', 'public'))->url($setting->value) }}"
                        alt="Public Meta Image"
                        class="h-24 w-auto rounded border border-gray-200 dark:border-gray-700"
                    />
                </div>
            @endif
            <input
                type="file"
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                accept="image/*"
                class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-100 dark:hover:file:bg-gray-600"
            >
        @elseif($setting->key === 'billing_currency')
            @php
                $popularCurrencies = [
                    'USD' => 'USD - US Dollar',
                    'EUR' => 'EUR - Euro',
                    'GBP' => 'GBP - British Pound',
                    'CAD' => 'CAD - Canadian Dollar',
                    'AUD' => 'AUD - Australian Dollar',
                    'NZD' => 'NZD - New Zealand Dollar',
                    'JPY' => 'JPY - Japanese Yen',
                    'CNY' => 'CNY - Chinese Yuan',
                    'INR' => 'INR - Indian Rupee',
                    'SGD' => 'SGD - Singapore Dollar',
                ];

                $billingCurrencyValue = is_string($setting->value) ? strtoupper(trim((string) $setting->value)) : '';
            @endphp
            <input
                type="text"
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                value="{{ old($setting->key, $billingCurrencyValue) }}"
                maxlength="3"
                list="billing_currency_options"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
            <datalist id="billing_currency_options">
                @foreach($popularCurrencies as $currencyCode => $currencyLabel)
                    <option value="{{ $currencyCode }}" label="{{ $currencyLabel }}"></option>
                @endforeach
            </datalist>
        @elseif($setting->key === 'site_language')
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                @foreach(($activeLocales ?? []) as $loc)
                    <option value="{{ $loc->code }}" {{ (string) $setting->value === (string) $loc->code ? 'selected' : '' }}>
                        {{ $loc->code }} — {{ $loc->name }}
                    </option>
                @endforeach
            </select>
        @elseif($setting->key === 'admin_font_family')
            @php
                $adminFontFamilies = [];
                $cfgFamilies = config('mailzen.fonts.supported_google_families', []);
                if (is_array($cfgFamilies)) {
                    foreach ($cfgFamilies as $fontName) {
                        if (is_string($fontName) && trim($fontName) !== '') {
                            $adminFontFamilies[trim((string) $fontName)] = trim((string) $fontName);
                        }
                    }
                }

                $adminFontFamilyValue = is_string($setting->value) ? trim($setting->value) : '';
                $adminFontFamilyValue = $adminFontFamilyValue !== '' ? $adminFontFamilyValue : 'Inter';
                $adminFontFamilyIsCustom = !array_key_exists($adminFontFamilyValue, $adminFontFamilies);
            @endphp
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                @if($adminFontFamilyIsCustom)
                    <option value="{{ $adminFontFamilyValue }}" selected>
                        {{ $adminFontFamilyValue }}
                    </option>
                @endif
                @foreach($adminFontFamilies as $fontValue => $fontLabel)
                    <option value="{{ $fontValue }}" {{ (string) $adminFontFamilyValue === (string) $fontValue ? 'selected' : '' }}>
                        {{ $fontLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($setting->key === 'default_storage_driver')
            @php
                $storageDrivers = [
                    'local' => 'Local',
                    's3' => 'Amazon S3',
                    'wasabi' => 'Wasabi (S3 Compatible)',
                    'gcs' => 'Google Cloud Storage',
                ];
            @endphp
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                @foreach($storageDrivers as $driverKey => $driverLabel)
                    <option value="{{ $driverKey }}" {{ (string) $setting->value === (string) $driverKey ? 'selected' : '' }}>
                        {{ $driverLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($setting->key === 'default_customer_group_id')
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                <option value="" {{ empty($setting->value) ? 'selected' : '' }}>None</option>
                @foreach(($customerGroups ?? []) as $groupId => $groupName)
                    <option value="{{ $groupId }}" {{ (string) $setting->value === (string) $groupId ? 'selected' : '' }}>
                        {{ $groupName }}
                    </option>
                @endforeach
            </select>
        @elseif($setting->key === 'new_registered_customer_group_id')
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                <option value="" {{ empty($setting->value) ? 'selected' : '' }}>None</option>
                @foreach(($customerGroups ?? []) as $groupId => $groupName)
                    <option value="{{ $groupId }}" {{ (string) $setting->value === (string) $groupId ? 'selected' : '' }}>
                        {{ $groupName }}
                    </option>
                @endforeach
            </select>
        @elseif(in_array($setting->key, ['transactional_delivery_server_id', 'verification_delivery_server_id', 'password_reset_delivery_server_id'], true))
            @php
                $deliveryServerSelectOptions = $deliveryServerOptions ?? [];
                if ($setting->key !== 'transactional_delivery_server_id') {
                    $deliveryServerSelectOptions = array_merge(['inherit' => 'Inherit (Transactional Default)'], $deliveryServerSelectOptions);
                }

                $deliveryServerSelectValue = is_string($setting->value) ? trim($setting->value) : '';
            @endphp
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                @foreach(($deliveryServerSelectOptions ?? []) as $optValue => $optLabel)
                    <option value="{{ $optValue }}" {{ (string) $deliveryServerSelectValue === (string) $optValue ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($setting->key === 'from_email')
            <input
                type="email"
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                value="{{ $setting->value }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
        @elseif($setting->key === 'google_client_secret')
            <div class="relative" data-secret-wrapper>
                <input
                    type="password"
                    name="{{ $setting->key }}"
                    id="setting_{{ $setting->key }}"
                    value="{{ !empty($setting->value) ? '********' : '' }}"
                    autocomplete="new-password"
                    data-secret-url="{{ route('admin.settings.secret', ['key' => $setting->key]) }}"
                    data-secret-input
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                >

                <button
                    type="button"
                    data-toggle-secret
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                    aria-label="{{ __('Toggle secret visibility') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
        @elseif(in_array($setting->key, ['openai_api_key', 'gemini_api_key'], true))
            <div class="relative" data-secret-wrapper>
                <input
                    type="password"
                    name="{{ $setting->key }}"
                    id="setting_{{ $setting->key }}"
                    value="{{ !empty($setting->value) ? '********' : '' }}"
                    autocomplete="new-password"
                    placeholder="Leave blank to keep current key"
                    data-secret-url="{{ route('admin.settings.secret', ['key' => $setting->key]) }}"
                    data-secret-input
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                >

                <button
                    type="button"
                    data-toggle-secret
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                    aria-label="{{ __('Toggle secret visibility') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
        @elseif($setting->key === 'google_redirect_uri')
            <input
                type="url"
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                value="{{ $setting->value }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >

            @php
                $googleRedirectUris = array_values(array_unique(array_filter([
                    route('customer.auth.google.callback'),
                    route('customer.integrations.google.callback', ['service' => 'sheets']),
                    route('customer.integrations.google.callback', ['service' => 'drive']),
                    route('admin.auth.google.callback'),
                ], fn ($v) => is_string($v) && trim($v) !== '')));
            @endphp

            <div class="mt-3">
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Authorized redirect URIs (add all in Google Cloud Console)') }}</div>
                <div class="mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 font-mono text-xs whitespace-pre-wrap break-all">{{ implode("\n", $googleRedirectUris) }}</div>
            </div>
        @elseif($setting->key === 'email_verification_message' || $setting->key === 'password_reset_message')
            <textarea
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                rows="4"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >{{ $setting->value }}</textarea>
        @elseif($setting->key === 'gdpr_notice_description')
            <textarea
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                rows="4"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >{{ $setting->value }}</textarea>
        @elseif($setting->key === 'gdpr_notice_position')
            @php
                $gdprPositionValue = is_string($setting->value) ? trim($setting->value) : '';
                $gdprPositions = [
                    'bottom_left' => 'Bottom Left',
                    'bottom_right' => 'Bottom Right',
                    'bottom_full_width' => 'Bottom Full Width',
                ];
                if (!array_key_exists($gdprPositionValue, $gdprPositions)) {
                    $gdprPositionValue = 'bottom_full_width';
                }
            @endphp
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                @foreach($gdprPositions as $posValue => $posLabel)
                    <option value="{{ $posValue }}" {{ (string) $gdprPositionValue === (string) $posValue ? 'selected' : '' }}>
                        {{ $posLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($setting->type === 'boolean')
            <div class="flex items-center">
                <input type="hidden" name="{{ $setting->key }}" value="0">
                <label class="inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        name="{{ $setting->key }}"
                        id="setting_{{ $setting->key }}"
                        value="1"
                        {{ $setting->value ? 'checked' : '' }}
                        class="sr-only peer"
                    >
                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:bg-gray-700 peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                </label>
            </div>
        @elseif($setting->key === 'storage_driver')
            @php
                $storageDrivers = [
                    'local' => 'Local',
                    's3' => 'Amazon S3',
                    'wasabi' => 'Wasabi (S3 Compatible)',
                    'gcs' => 'Google Cloud Storage',
                ];
            @endphp
            <select
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
                @foreach($storageDrivers as $driverKey => $driverLabel)
                    <option value="{{ $driverKey }}" {{ (string) $setting->value === (string) $driverKey ? 'selected' : '' }}>
                        {{ $driverLabel }}
                    </option>
                @endforeach
            </select>
        @elseif(in_array($setting->key, ['google_client_secret', 's3_secret', 'wasabi_secret', 'openai_api_key', 'gemini_api_key'], true))
            <div class="relative" data-secret-wrapper>
                <input
                    type="password"
                    name="{{ $setting->key }}"
                    id="setting_{{ $setting->key }}"
                    value="{{ !empty($setting->value) ? '********' : '' }}"
                    autocomplete="new-password"
                    data-secret-url="{{ route('admin.settings.secret', ['key' => $setting->key]) }}"
                    data-secret-input
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm pr-10"
                >

                <button
                    type="button"
                    data-toggle-secret
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 dark:text-gray-300"
                    aria-label="{{ __('Toggle secret visibility') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
        @elseif($setting->type === 'json' || $setting->type === 'array')
            <textarea
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                rows="4"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
        @elseif($setting->type === 'integer')
            <input
                type="number"
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                value="{{ $setting->value }}"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
        @else
            <input
                type="text"
                name="{{ $setting->key }}"
                id="setting_{{ $setting->key }}"
                value="{{ $setting->value }}"
                @if($setting->key === 'storage_url_prefix') placeholder="public" @endif
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
            >
        @endif
    </div>
    @endif

    @error($setting->key)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
