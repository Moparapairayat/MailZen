@extends('layouts.customer')

@section('title', 'A/B Test - ' . $campaign->name)
@section('page-title', 'A/B Test: ' . $campaign->name)

@section('content')
<div class="space-y-6">
    <!-- Back to Campaign -->
    <div>
        <a href="{{ route('customer.campaigns.show', $campaign) }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">
            ← Back to Campaign
        </a>
    </div>

    @if($campaign->variants->isEmpty())
        <!-- Create A/B Test Variants -->
        <x-card title="Create A/B Test">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Create multiple variants of your campaign to test which performs better. You can test different subject lines, content, or both.
            </p>

            <form method="POST" action="{{ route('customer.campaigns.ab-test.store', $campaign) }}" id="ab-test-form">
                @csrf
                
                <div id="variants-container" class="space-y-6">
                    <!-- Variant A -->
                    <div class="variant-item border border-gray-200 dark:border-gray-700 rounded-lg p-6" data-variant="0">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Variant A</h3>
                            <span class="text-sm text-gray-500">Required</span>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Variant Name</label>
                                <input type="text" name="variants[0][name]" value="Variant A" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject Line</label>
                                <input type="text" name="variants[0][subject]" value="{{ $campaign->subject }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to use campaign subject</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">HTML Content</label>
                                <textarea name="variants[0][html_content]" rows="8" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm font-mono">{{ $campaign->html_content }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Leave empty to use campaign content</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Split Percentage</label>
                                <input type="number" name="variants[0][split_percentage]" value="50" min="1" max="100" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Percentage of audience for this variant</p>
                            </div>
                        </div>
                    </div>

                    <!-- Variant B -->
                    <div class="variant-item border border-gray-200 dark:border-gray-700 rounded-lg p-6" data-variant="1">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Variant B</h3>
                            <span class="text-sm text-gray-500">Required</span>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Variant Name</label>
                                <input type="text" name="variants[1][name]" value="Variant B" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject Line</label>
                                <input type="text" name="variants[1][subject]" value="{{ $campaign->subject }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to use campaign subject</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">HTML Content</label>
                                <textarea name="variants[1][html_content]" rows="8" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm font-mono">{{ $campaign->html_content }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Leave empty to use campaign content</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Split Percentage</label>
                                <input type="number" name="variants[1][split_percentage]" value="50" min="1" max="100" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Percentage of audience for this variant</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>Total Split:</strong> <span id="total-split">100</span>%
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">
                        Split percentages must add up to exactly 100%
                    </p>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <x-button href="{{ route('customer.campaigns.show', $campaign) }}" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Save A/B Test</x-button>
                </div>
            </form>
        </x-card>
    @else
        <!-- A/B Test Results -->
        <x-card title="A/B Test Results">
            <div class="mb-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Campaign Status: <span class="font-semibold">{{ ucfirst($campaign->status) }}</span>
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6">
                @foreach($campaign->variants as $variant)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 {{ $variant->is_winner ? 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/20' : '' }}">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $variant->name }}
                                    @if($variant->is_winner)
                                        <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">Winner</span>
                                    @endif
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $variant->split_percentage }}% of audience
                                </p>
                            </div>
                            @if($campaign->status === 'completed' && !$variant->is_winner)
                                <form method="POST" action="{{ route('customer.campaigns.variants.select-winner', [$campaign, $variant]) }}" class="inline">
                                    @csrf
                                    <x-button type="submit" variant="primary" size="sm">Select as Winner</x-button>
                                </form>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($variant->sent_count) }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Rate</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($variant->open_rate, 1) }}%</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Click Rate</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($variant->click_rate, 1) }}%</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Bounce Rate</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($variant->bounce_rate, 1) }}%</div>
                            </div>
                        </div>

                        @if($variant->subject)
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Subject Line</div>
                                <div class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $variant->subject }}</div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($campaign->status === 'completed')
                <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Next Step:</strong> Review the results above and select the winning variant. You can then send the winning variant to the remaining audience.
                    </p>
                </div>
            @endif
        </x-card>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('ab-test-form');
    if (!form) return;

    function updateTotalSplit() {
        const inputs = form.querySelectorAll('input[name*="[split_percentage]"]');
        let total = 0;
        inputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });
        document.getElementById('total-split').textContent = total;
        
        // Highlight if not 100%
        const totalSplitEl = document.getElementById('total-split');
        if (total === 100) {
            totalSplitEl.parentElement.classList.remove('bg-red-50', 'border-red-200');
            totalSplitEl.parentElement.classList.add('bg-blue-50', 'border-blue-200');
        } else {
            totalSplitEl.parentElement.classList.remove('bg-blue-50', 'border-blue-200');
            totalSplitEl.parentElement.classList.add('bg-red-50', 'border-red-200');
        }
    }

    form.querySelectorAll('input[name*="[split_percentage]"]').forEach(input => {
        input.addEventListener('input', updateTotalSplit);
    });

    form.addEventListener('submit', function(e) {
        const inputs = form.querySelectorAll('input[name*="[split_percentage]"]');
        let total = 0;
        inputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });
        
        if (total !== 100) {
            e.preventDefault();
            alert('Split percentages must add up to exactly 100%. Current total: ' + total + '%');
            return false;
        }
    });

    updateTotalSplit();
});
</script>
@endpush
@endsection

