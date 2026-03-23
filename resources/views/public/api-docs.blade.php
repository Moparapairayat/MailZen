@extends('layouts.public')

@section('title', 'API Docs')

@section('content')
    <div class="min-h-[calc(100vh-64px)] bg-white dark:bg-gray-900">
        <div id="api-docs" class="h-[calc(100vh-64px)]">
            <!-- Loading indicator -->
            <div id="loading-indicator" class="flex items-center justify-center h-full">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-500 dark:text-gray-400">Loading API documentation...</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
        <script>
            (function () {
                const container = document.getElementById('api-docs');
                if (!container) return;

                if (window.__mailpurseScalarPublicApiDocsInitialized) {
                    return;
                }

                const showError = (message) => {
                    const loadingIndicator = document.getElementById('loading-indicator');
                    if (!loadingIndicator) return;
                    loadingIndicator.innerHTML = '<div class="text-center"><p class="text-gray-500 dark:text-gray-400 mb-4">' + String(message || 'Failed to load API documentation.') + '</p><button onclick="window.location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Reload</button></div>';
                };

                const hideLoader = () => {
                    const loadingIndicator = document.getElementById('loading-indicator');
                    if (loadingIndicator) {
                        loadingIndicator.style.display = 'none';
                    }
                };

                const probeSpecUrl = async (url) => {
                    try {
                        const res = await fetch(url, {
                            method: 'GET',
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });

                        if (!res || !res.ok) {
                            return null;
                        }

                        const ct = (res.headers && res.headers.get) ? (res.headers.get('content-type') || '') : '';
                        const text = await res.text();
                        const looksJson = ct.toLowerCase().includes('application/json') || (text && text.trim().startsWith('{'));
                        if (!looksJson) {
                            return null;
                        }

                        return url;
                    } catch (e) {
                        return null;
                    }
                };

                const initScalar = async (retries = 0) => {
                    if (typeof Scalar === 'undefined') {
                        if (retries < 10) {
                            setTimeout(() => initScalar(retries + 1), 500);
                            return;
                        }
                        showError('API documentation library failed to load.');
                        return;
                    }

                    const candidates = [
                        '{{ url('/openapi') }}',
                        '{{ url('/openapi.json') }}',
                        '{{ url('/api/openapi.json') }}',
                    ];

                    let specUrl = null;
                    for (const candidate of candidates) {
                        specUrl = await probeSpecUrl(candidate);
                        if (specUrl) break;
                    }

                    if (!specUrl) {
                        showError('OpenAPI spec endpoint is not returning JSON. Please check /openapi and /openapi.json.');
                        return;
                    }

                    try {
                        window.__mailpurseScalarPublicApiDocsInitialized = true;
                        hideLoader();
                        Scalar.createApiReference('#api-docs', {
                            url: specUrl,
                            customCss: `
                                .sidebar { position: sticky; top: 0; height: 100vh; overflow: auto; }
                            `,
                        });
                    } catch (error) {
                        showError('Failed to initialize API documentation.');
                    }
                };

                initScalar();
            })();
        </script>
    @endpush
@endsection
