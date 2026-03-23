<!DOCTYPE html>
@php
    $dirSetting = (string) data_get($form->settings, 'direction', 'auto');
    $dirResolved = in_array($dirSetting, ['ltr', 'rtl'], true) ? $dirSetting : app('locale.direction')->dir();
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dirResolved }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Subscribe - {{ $form->title ?: $form->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $isEmbed = request()->boolean('embed');
@endphp

<body class="font-sans antialiased {{ $isEmbed ? 'bg-transparent' : 'bg-gray-50' }}">
    <div class="{{ $isEmbed ? 'flex items-start justify-center py-8 px-4 sm:px-6' : 'min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8' }}">
        <div class="max-w-md w-full {{ $isEmbed ? 'space-y-6' : 'space-y-8' }}">
            @php
                $showTitle = data_get($form->settings, 'show_title', true);
                $showDescription = data_get($form->settings, 'show_description', true);
            @endphp

            @if($showTitle || ($showDescription && $form->description))
                <div>
                    @if($showTitle)
                        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                            {{ $form->title ?: $form->name }}
                        </h2>
                    @endif
                    @if($showDescription && $form->description)
                        <p class="mt-2 text-center text-sm text-gray-600">
                            {{ $form->description }}
                        </p>
                    @endif
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('public.subscribe.store', $form->slug) }}"
                class="mt-8 space-y-6 mp-subscribe-form"
                x-data="subscribeForm('{{ route('public.subscribe.api', $form->slug) }}')"
                @submit.prevent="submit($event)"
            >
                @csrf

                @if(session('success'))
                    <div class="rounded-md bg-green-50 p-4">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded-md bg-red-50 p-4">
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-md bg-red-50 p-4">
                        <ul class="list-disc pl-5 text-sm text-red-800">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $selectedFields = is_array($form->fields) ? $form->fields : ['email'];
                    if (!in_array('email', $selectedFields, true)) {
                        array_unshift($selectedFields, 'email');
                    }

                    $customDefs = is_array($form->emailList?->custom_fields) ? $form->emailList?->custom_fields : [];
                    $customDefsByKey = [];
                    foreach ($customDefs as $def) {
                        if (!is_array($def)) {
                            continue;
                        }
                        $k = trim((string) ($def['key'] ?? ''));
                        if ($k === '') {
                            continue;
                        }
                        $customDefsByKey[$k] = $def;
                    }

                    $fieldStyles = data_get($form->settings, 'field_styles', []);
                    if (!is_array($fieldStyles)) {
                        $fieldStyles = [];
                    }

                    $fieldMeta = data_get($form->settings, 'field_meta', []);
                    if (!is_array($fieldMeta)) {
                        $fieldMeta = [];
                    }

                    $iconSvg = function (string $name): string {
                        $icons = [
                            'mail' => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z" fill="none"></path><path d="M22 6l-10 7L2 6"></path></svg>',
                            'user' => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
                            'lock' => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
                            'tag' => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41L11 3H4v7l9.59 9.59a2 2 0 0 0 2.82 0l4.18-4.18a2 2 0 0 0 0-2.82z"></path><circle cx="7.5" cy="7.5" r="1.5"></circle></svg>',
                            'message' => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path></svg>',
                            'phone' => '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3 5.18 2 2 0 0 1 5.11 3h3a2 2 0 0 1 2 1.72c.12.86.31 1.7.57 2.5a2 2 0 0 1-.45 2.11L9.91 10.09a16 16 0 0 0 4 4l.76-.32a2 2 0 0 1 2.11.45c.8.26 1.64.45 2.5.57A2 2 0 0 1 22 16.92z"></path></svg>',
                        ];

                        return $icons[$name] ?? '';
                    };

                    $buildCss = function (array $style): string {
                        $parts = [];

                        $p = trim((string) ($style['padding'] ?? ''));
                        if ($p !== '') {
                            $parts[] = 'padding:' . $p . ' !important';
                        }

                        $bg = trim((string) ($style['background'] ?? ''));
                        if ($bg !== '') {
                            $parts[] = 'background:' . $bg . ' !important';
                        }

                        $tc = trim((string) ($style['text_color'] ?? ''));
                        if ($tc !== '') {
                            $parts[] = 'color:' . $tc . ' !important';
                        }

                        $pc = trim((string) ($style['placeholder_color'] ?? ''));
                        if ($pc !== '') {
                            $parts[] = '--mp-placeholder-color:' . $pc;
                        }

                        $b = trim((string) ($style['border'] ?? ''));
                        if ($b !== '') {
                            $parts[] = 'border:' . $b . ' !important';
                        }

                        $br = trim((string) ($style['border_radius'] ?? ''));
                        if ($br !== '') {
                            $parts[] = 'border-radius:' . $br . ' !important';
                        }

                        $sh = trim((string) ($style['shadow'] ?? ''));
                        if ($sh !== '') {
                            $parts[] = 'box-shadow:' . $sh . ' !important';
                        }

                        return implode(';', $parts);
                    };

                    $css = '';
                    $appendCss = function (string $id, string $pseudo, array $style) use (&$css, $buildCss) {
                        $rule = $buildCss($style);
                        if (trim($rule) === '') {
                            return;
                        }
                        $css .= '#' . $id . $pseudo . '{' . $rule . '}';
                    };

                    $css .= '.mp-subscribe-form input::placeholder,.mp-subscribe-form textarea::placeholder{color:var(--mp-placeholder-color) !important;}';
                @endphp

                <div class="space-y-4">
                    @foreach($selectedFields as $fieldKey)
                        @php
                            $id = 'mpf_' . preg_replace('/[^a-zA-Z0-9_]+/', '_', (string) $fieldKey);
                            $styleNormal = is_array(data_get($fieldStyles, $fieldKey . '.normal')) ? data_get($fieldStyles, $fieldKey . '.normal') : [];
                            $styleHover = is_array(data_get($fieldStyles, $fieldKey . '.hover')) ? data_get($fieldStyles, $fieldKey . '.hover') : [];
                            $styleFocus = is_array(data_get($fieldStyles, $fieldKey . '.focus')) ? data_get($fieldStyles, $fieldKey . '.focus') : [];

                            $fm = is_array(data_get($fieldMeta, $fieldKey)) ? data_get($fieldMeta, $fieldKey) : [];
                            $showLabel = (bool) data_get($fm, 'show_label', true);
                            $iconName = trim((string) data_get($fm, 'icon', ''));
                            $iconHtml = $iconName !== '' ? $iconSvg($iconName) : '';
                            $iconColor = trim((string) data_get($fm, 'icon_color', ''));
                            $iconUploadUrl = trim((string) data_get($fm, 'icon_upload_url', ''));
                            $iconIsUploaded = $iconUploadUrl !== '';
                            $rowClass = ($showLabel ? 'mt-1 ' : '') . 'relative';

                            $wrapperStyle = '';
                            $m = trim((string) ($styleNormal['margin'] ?? ''));
                            if ($m !== '') {
                                $wrapperStyle = 'margin:' . $m;
                            }
                            $inputStyle = $buildCss($styleNormal);

                            $appendCss($id, '', $styleNormal);
                            $appendCss($id, ':hover', $styleHover);
                            $appendCss($id, ':focus', $styleFocus);

                            if ($iconIsUploaded || $iconHtml !== '') {
                                $pad = trim((string) ($styleNormal['padding'] ?? ''));
                                $left = '0.75rem';
                                if ($pad !== '') {
                                    $parts = preg_split('/\s+/', $pad) ?: [];
                                    $count = count($parts);
                                    if ($count === 1) {
                                        $left = (string) $parts[0];
                                    } elseif ($count === 2) {
                                        $left = (string) $parts[1];
                                    } elseif ($count === 3) {
                                        $left = (string) $parts[1];
                                    } elseif ($count >= 4) {
                                        $left = (string) $parts[3];
                                    }
                                }
                                $css .= '#' . $id . '{padding-inline-start:calc(' . $left . ' + 2rem) !important;}';
                            }
                        @endphp

                        @if($fieldKey === 'email')
                            <div style="{{ e($wrapperStyle) }}">
                                @if($showLabel)
                                    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                                @endif
                                <div class="{{ $rowClass }}">
                                    @if($iconIsUploaded)
                                        <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;">
                                            <img src="{{ e($iconUploadUrl) }}" alt="" style="width:1rem;height:1rem;object-fit:contain;">
                                        </span>
                                    @elseif($iconHtml !== '')
                                        <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;{{ $iconColor !== '' ? 'color:' . e($iconColor) . ';' : '' }}">{!! $iconHtml !!}</span>
                                    @endif
                                    <input
                                        id="{{ $id }}"
                                        name="email"
                                        type="email"
                                        required
                                        value="{{ old('email') }}"
                                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none sm:text-sm"
                                        placeholder="Email"
                                    >
                                </div>
                            </div>
                        @elseif($fieldKey === 'first_name')
                            <div style="{{ e($wrapperStyle) }}">
                                @if($showLabel)
                                    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">First Name</label>
                                @endif
                                <div class="{{ $rowClass }}">
                                    @if($iconIsUploaded)
                                        <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;">
                                            <img src="{{ e($iconUploadUrl) }}" alt="" style="width:1rem;height:1rem;object-fit:contain;">
                                        </span>
                                    @elseif($iconHtml !== '')
                                        <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;{{ $iconColor !== '' ? 'color:' . e($iconColor) . ';' : '' }}">{!! $iconHtml !!}</span>
                                    @endif
                                    <input
                                        id="{{ $id }}"
                                        name="first_name"
                                        type="text"
                                        value="{{ old('first_name') }}"
                                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none sm:text-sm"
                                        placeholder="First Name"
                                    >
                                </div>
                            </div>
                        @elseif($fieldKey === 'last_name')
                            <div style="{{ e($wrapperStyle) }}">
                                @if($showLabel)
                                    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">Last Name</label>
                                @endif
                                <div class="{{ $rowClass }}">
                                    @if($iconIsUploaded)
                                        <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;">
                                            <img src="{{ e($iconUploadUrl) }}" alt="" style="width:1rem;height:1rem;object-fit:contain;">
                                        </span>
                                    @elseif($iconHtml !== '')
                                        <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;{{ $iconColor !== '' ? 'color:' . e($iconColor) . ';' : '' }}">{!! $iconHtml !!}</span>
                                    @endif
                                    <input
                                        id="{{ $id }}"
                                        name="last_name"
                                        type="text"
                                        value="{{ old('last_name') }}"
                                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none sm:text-sm"
                                        placeholder="Last Name"
                                    >
                                </div>
                            </div>
                        @elseif(is_string($fieldKey) && str_starts_with($fieldKey, 'cf:'))
                            @php
                                $k = trim(substr($fieldKey, 3));
                                $def = $customDefsByKey[$k] ?? [];
                                $label = trim((string) ($def['label'] ?? $k));
                                $type = (string) ($def['type'] ?? 'text');
                                $required = (bool) ($def['required'] ?? false);
                                $name = 'cf_' . $k;
                            @endphp
                            @if($k !== '')
                                <div style="{{ e($wrapperStyle) }}">
                                    @if($showLabel)
                                        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">{{ $label }}@if($required) <span class="text-red-500">*</span>@endif</label>
                                    @endif
                                    @if($type === 'textarea')
                                        <div class="{{ $rowClass }}">
                                            @if($iconIsUploaded)
                                                <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;">
                                                    <img src="{{ e($iconUploadUrl) }}" alt="" style="width:1rem;height:1rem;object-fit:contain;">
                                                </span>
                                            @elseif($iconHtml !== '')
                                                <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;{{ $iconColor !== '' ? 'color:' . e($iconColor) . ';' : '' }}">{!! $iconHtml !!}</span>
                                            @endif
                                            <textarea
                                                id="{{ $id }}"
                                                name="{{ $name }}"
                                                rows="3"
                                                @if($required) required @endif
                                                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none sm:text-sm"
                                                placeholder="{{ $label }}"
                                            >{{ old($name) }}</textarea>
                                        </div>
                                    @else
                                        <div class="{{ $rowClass }}">
                                            @if($iconIsUploaded)
                                                <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;">
                                                    <img src="{{ e($iconUploadUrl) }}" alt="" style="width:1rem;height:1rem;object-fit:contain;">
                                                </span>
                                            @elseif($iconHtml !== '')
                                                <span class="text-gray-400" style="position:absolute;inset-block-start:0;inset-block-end:0;inset-inline-start:0.75rem;display:flex;align-items:center;pointer-events:none;{{ $iconColor !== '' ? 'color:' . e($iconColor) . ';' : '' }}">{!! $iconHtml !!}</span>
                                            @endif
                                            <input
                                                id="{{ $id }}"
                                                name="{{ $name }}"
                                                type="text"
                                                @if($required) required @endif
                                                value="{{ old($name) }}"
                                                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 focus:outline-none sm:text-sm"
                                                placeholder="{{ $label }}"
                                            >
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif
                    @endforeach
                </div>

                @if($form->gdpr_checkbox)
                    <div class="flex items-center">
                        <input
                            id="gdpr_consent"
                            name="gdpr_consent"
                            type="checkbox"
                            required
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="gdpr_consent" class="ml-2 block text-sm text-gray-900">
                            {!! $form->gdpr_text ?? 'I agree to the processing of my personal data.' !!}
                        </label>
                    </div>
                @endif

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        <span x-show="!loading">Subscribe</span>
                        <span x-cloak x-show="loading">Submitting...</span>
                    </button>
                    <div
                        x-cloak
                        x-show="notice"
                        class="mt-3 rounded-md px-3 py-2 text-sm"
                        :class="noticeType === 'success'
                            ? 'bg-green-50 text-green-800'
                            : noticeType === 'exists'
                                ? 'bg-blue-50 text-blue-800'
                                : 'bg-red-50 text-red-800'"
                        x-text="notice"
                    ></div>
                </div>

                @if(trim($css) !== '')
                    <style>{!! $css !!}</style>
                @endif
            </form>
        </div>
    </div>

    <script>
        function subscribeForm(apiUrl) {
            return {
                loading: false,
                notice: '',
                noticeType: 'success',

                async submit(event) {
                    const formEl = event?.target;

                    // Fallback to normal form submission if axios isn't available
                    if (!formEl || !window.axios) {
                        formEl?.submit();
                        return;
                    }

                    this.loading = true;
                    this.notice = '';
                    this.noticeType = 'success';

                    try {
                        const fd = new FormData(formEl);
                        const payload = Object.fromEntries(fd.entries());

                        const res = await window.axios.post(apiUrl, payload, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        this.notice = res?.data?.message || 'Successfully subscribed.';
                        this.noticeType = res?.data?.status || 'success';

                        // Optional: clear fields on success
                        if (this.noticeType === 'success') {
                            const emailEl = formEl.querySelector('input[name="email"]');
                            if (emailEl) {
                                emailEl.value = '';
                            }
                        }
                    } catch (e) {
                        const status = e?.response?.status;
                        const data = e?.response?.data;

                        this.noticeType = 'error';

                        if (status === 422 && data?.errors) {
                            const firstField = Object.keys(data.errors)[0];
                            const firstMsg = firstField ? data.errors[firstField]?.[0] : null;
                            this.notice = firstMsg || 'Validation failed.';
                        } else {
                            this.notice = data?.message || 'Failed to submit. Please try again.';
                        }
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</body>
</html>

