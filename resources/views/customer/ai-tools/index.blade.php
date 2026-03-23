@extends('layouts.customer')

@section('title', 'AI Tools')
@section('page-title', 'AI Tools')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @customercan('ai_tools.permissions.can_use_email_text_generator')
            <a href="{{ route('customer.ai-tools.email-text-generator') }}" class="block rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Email Text Generator</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Generate subject + body from structured inputs.</div>
            </a>
        @endcustomercan
    </div>

    <x-card :padding="false">
        <div class="px-6 py-4 flex items-center justify-between gap-4">
            <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">History</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Your recent AI tool generations.</div>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tool</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Provider / Model</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tokens</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Output</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-950 divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse(($generations ?? null) as $g)
                            @php($output = (string) ($g->output ?? ''))
                            @php($outputId = 'ai-output-' . (string) $g->id)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ str_replace('_', ' ', (string) $g->tool) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ (string) $g->provider }}
                                    @if(is_string($g->model ?? null) && trim((string) $g->model) !== '')
                                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ (string) $g->model }}</div>
                                    @endif
                                    @if($g->used_admin_keys)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">admin keys</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($g->success)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">Success</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200">Failed</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $g->tokens_used !== null ? (int) $g->tokens_used : '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    {{ $g->created_at ? $g->created_at->format('Y-m-d H:i') : '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    @if($g->success && $output !== '')
                                        <details class="max-w-[44rem]">
                                            <summary class="cursor-pointer select-none">
                                                {{ \Illuminate\Support\Str::limit($output, 120) }}
                                            </summary>
                                            <div class="mt-2">
                                                <textarea id="{{ $outputId }}" readonly rows="8" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">{{ $output }}</textarea>
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit((string) ($g->error_message ?? ''), 140) ?: '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                    @if($g->success && $output !== '')
                                        <button type="button" class="copy-ai-output text-xs font-medium px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-950 hover:bg-gray-50 dark:hover:bg-gray-900" data-output-id="{{ $outputId }}">Copy</button>
                                        @customercan('templates.permissions.can_create_templates')
                                            <button type="button" class="export-ai-output ml-2 text-xs font-medium px-3 py-1.5 rounded-md border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-950 hover:bg-gray-50 dark:hover:bg-gray-900" data-output-id="{{ $outputId }}">Export</button>
                                        @endcustomercan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-600 dark:text-gray-400">No history yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(($generations ?? null) && method_exists($generations, 'links'))
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $generations->links() }}
            </div>
        @endif
    </x-card>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function getCsrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        document.querySelectorAll('.copy-ai-output').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                var id = btn.getAttribute('data-output-id') || '';
                var el = id ? document.getElementById(id) : null;
                var text = el ? (el.value || '') : '';
                try {
                    await navigator.clipboard.writeText(text);
                    var old = btn.textContent;
                    btn.textContent = 'Copied';
                    setTimeout(function () { btn.textContent = old; }, 1000);
                } catch (e) {
                    alert('Copy failed.');
                }
            });
        });

        document.querySelectorAll('.export-ai-output').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-output-id') || '';
                var el = id ? document.getElementById(id) : null;
                var text = String(el ? (el.value || '') : '').trim();
                if (!text) return;

                var ok = window.confirm('Export this generated text to Templates?');
                if (!ok) return;

                btn.disabled = true;
                var old = btn.textContent;
                btn.textContent = 'Exporting...';

                fetch(@js(route('customer.ai-tools.email-text-generator.export-to-template')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify({ text: text })
                }).then(async function (res) {
                    var data = null;
                    try {
                        data = await res.json();
                    } catch (e) {
                        data = null;
                    }

                    btn.disabled = false;
                    btn.textContent = old;

                    if (!res.ok || !data || data.success !== true || typeof data.view_url !== 'string') {
                        alert((data && typeof data.message === 'string') ? data.message : 'Failed to export.');
                        return;
                    }

                    window.location.href = data.view_url;
                }).catch(function () {
                    btn.disabled = false;
                    btn.textContent = old;
                    alert('Failed to export.');
                });
            });
        });
    });
</script>
@endsection
