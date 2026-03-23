@extends('layouts.customer')

@section('title', 'Import Subscribers')
@section('page-title', 'Import Subscribers: ' . $list->name)

@section('content')
<div class="max-w-4xl" x-data="importSubscribers()">
    <x-card title="Import Subscribers from CSV">
        <form method="POST" action="{{ route('customer.lists.subscribers.import.store', $list) }}" enctype="multipart/form-data" class="space-y-6" @submit.prevent="startAjaxImport()">
            @csrf

            <!-- File Upload -->
            <div>
                <label for="csv_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    CSV File <span class="text-red-500">*</span>
                </label>
                <input
                    type="file"
                    name="csv_file"
                    id="csv_file"
                    accept=".csv,.txt"
                    required
                    @change="handleFileSelect($event)"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                >
                <p class="mt-1 text-sm text-gray-500">Upload a CSV file with subscriber data. Maximum file size: 10MB</p>
                @error('csv_file')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="uploading" class="space-y-2" x-cloak>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">Uploading your CSV file...</div>
                    <div class="text-sm text-gray-700 dark:text-gray-300" x-text="uploadProgress + '%'">0%</div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                    <div class="bg-green-600 h-3 rounded-full transition-all duration-300" :style="'width: ' + uploadProgress + '%'" style="width: 0%"></div>
                </div>
            </div>

            <div x-show="importStarted" class="space-y-4" x-cloak>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">Importing subscribers...</div>
                    <div class="text-sm text-gray-700 dark:text-gray-300" x-text="importPercent + '%'">0%</div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                    <div class="bg-blue-600 h-4 rounded-full transition-all duration-500" :style="'width: ' + importPercent + '%'" style="width: 0%"></div>
                </div>
                <div class="text-sm text-gray-700 dark:text-gray-300" x-text="importText">0 / 0 processed</div>

                <div x-show="importFailed" class="text-sm text-red-600 dark:text-red-400" x-text="importError" x-cloak></div>
                <div x-show="importCompleted" class="text-sm text-green-700 dark:text-green-300" x-text="importSuccess" x-cloak></div>

                <div x-show="importCompleted" class="pt-2" x-cloak>
                    <a href="{{ route('customer.lists.subscribers.index', $list) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Return to Subscribers List
                    </a>
                </div>
            </div>

            <!-- Column Mapping -->
            <div x-show="fileSelected" class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Map CSV Columns</h3>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="column_mapping_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email Column <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="column_mapping[email]"
                            id="column_mapping_email"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                            <option value="">Select column...</option>
                            <template x-for="column in csvColumns" :key="column">
                                <option :value="column" x-text="column"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="column_mapping_first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            First Name Column
                        </label>
                        <select
                            name="column_mapping[first_name]"
                            id="column_mapping_first_name"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                            <option value="">Select column...</option>
                            <template x-for="column in csvColumns" :key="column">
                                <option :value="column" x-text="column"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="column_mapping_last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Last Name Column
                        </label>
                        <select
                            name="column_mapping[last_name]"
                            id="column_mapping_last_name"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                        >
                            <option value="">Select column...</option>
                            <template x-for="column in csvColumns" :key="column">
                                <option :value="column" x-text="column"></option>
                            </template>
                        </select>
                    </div>

                    @php
                        $listCustomFields = is_array($list->custom_fields ?? null) ? $list->custom_fields : [];
                        $listCustomFields = array_values(array_filter($listCustomFields, function ($f) {
                            return is_array($f) && !empty($f['key']);
                        }));
                    @endphp

                    @foreach($listCustomFields as $field)
                        <div>
                            <label for="column_mapping_custom_fields_{{ $field['key'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $field['label'] ?? $field['key'] }} Column
                            </label>
                            <select
                                name="column_mapping[custom_fields][{{ $field['key'] }}]"
                                id="column_mapping_custom_fields_{{ $field['key'] }}"
                                data-custom-field-key="{{ $field['key'] }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                            >
                                <option value="">Select column...</option>
                                <template x-for="column in csvColumns" :key="column">
                                    <option :value="column" x-text="column"></option>
                                </template>
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Import Options -->
            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Import Options</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input
                            id="capture_unmapped"
                            name="column_mapping[capture_unmapped]"
                            type="checkbox"
                            value="1"
                            checked
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="capture_unmapped" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Import extra CSV columns as Custom Fields
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="add_list_custom_fields"
                            name="column_mapping[add_list_custom_fields]"
                            type="checkbox"
                            value="1"
                            checked
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="add_list_custom_fields" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Add imported Custom Fields to this Email List
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="skip_duplicates"
                            name="skip_duplicates"
                            type="checkbox"
                            value="1"
                            checked
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="skip_duplicates" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Skip duplicate emails
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="update_existing"
                            name="update_existing"
                            type="checkbox"
                            value="1"
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                        >
                        <label for="update_existing" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            Update existing subscribers
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('customer.lists.subscribers.index', $list) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" :disabled="importStarted || uploading">
                    Start Import
                </button>
            </div>
        </form>
    </x-card>
</div>

<script>
function importSubscribers() {
    return {
        fileSelected: false,
        csvColumns: [],
        selectedFile: null,

        uploading: false,
        uploadProgress: 0,

        importStarted: false,
        importId: null,
        importPercent: 0,
        importText: '0 / 0 processed',
        importCompleted: false,
        importFailed: false,
        importError: '',
        importSuccess: '',
        stepTimer: null,
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) {
                this.fileSelected = false;
                return;
            }

            this.selectedFile = file;
            this.importStarted = false;
            this.importCompleted = false;
            this.importFailed = false;
            this.importError = '';
            this.importSuccess = '';
            this.importPercent = 0;
            this.importText = '0 / 0 processed';
            this.importId = null;

            const reader = new FileReader();
            reader.onload = (e) => {
                const text = e.target.result;
                const lines = text.split('\n');
                if (lines.length > 0) {
                    // Parse CSV header
                    this.csvColumns = lines[0].split(',').map(col => col.trim().replace(/^"|"$/g, ''));
                    this.fileSelected = true;
                }
            };
            reader.readAsText(file);
        },

        startAjaxImport() {
            if (!this.selectedFile) {
                return;
            }

            if (this.importStarted || this.uploading) {
                return;
            }

            const emailColumn = document.getElementById('column_mapping_email')?.value || '';
            const firstNameColumn = document.getElementById('column_mapping_first_name')?.value || '';
            const lastNameColumn = document.getElementById('column_mapping_last_name')?.value || '';
            const captureUnmapped = document.getElementById('capture_unmapped')?.checked ? 1 : 0;
            const addListCustomFields = document.getElementById('add_list_custom_fields')?.checked ? 1 : 0;
            const skipDuplicates = document.getElementById('skip_duplicates')?.checked ? 1 : 0;
            const updateExisting = document.getElementById('update_existing')?.checked ? 1 : 0;

            if (!emailColumn) {
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('csv_file', this.selectedFile);
            formData.append('column_mapping[email]', emailColumn);
            if (firstNameColumn) {
                formData.append('column_mapping[first_name]', firstNameColumn);
            }
            if (lastNameColumn) {
                formData.append('column_mapping[last_name]', lastNameColumn);
            }

            document.querySelectorAll('select[data-custom-field-key]')
                .forEach((el) => {
                    const name = el.getAttribute('name');
                    const value = el.value || '';
                    if (!name || !value) {
                        return;
                    }
                    formData.append(name, value);
                });

            formData.append('column_mapping[capture_unmapped]', captureUnmapped);
            formData.append('column_mapping[add_list_custom_fields]', addListCustomFields);
            formData.append('skip_duplicates', skipDuplicates);
            formData.append('update_existing', updateExisting);

            this.uploading = true;
            this.uploadProgress = 0;
            this.importFailed = false;
            this.importError = '';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('customer.lists.subscribers.import.ajax.start', $list) }}', true);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.onprogress = (event) => {
                if (!event.lengthComputable) {
                    return;
                }
                const percent = Math.round((event.loaded / event.total) * 100);
                this.uploadProgress = Math.max(0, Math.min(100, percent));
            };

            xhr.onload = () => {
                this.uploading = false;
                this.uploadProgress = 100;

                let payload = null;
                try {
                    payload = JSON.parse(xhr.responseText);
                } catch (e) {
                    payload = null;
                }

                if (!payload || !payload.success) {
                    this.importFailed = true;
                    this.importError = (payload && payload.message) ? payload.message : 'Failed to start import.';
                    return;
                }

                this.importId = payload.import_id;
                this.importStarted = true;
                this.queueStep();
            };

            xhr.onerror = () => {
                this.uploading = false;
                this.importFailed = true;
                this.importError = 'Upload failed. Please try again.';
            };

            xhr.send(formData);
        },

        queueStep() {
            if (!this.importId) {
                return;
            }

            if (this.stepTimer) {
                clearTimeout(this.stepTimer);
            }

            this.stepTimer = setTimeout(() => this.stepOnce(), 600);
        },

        stepOnce() {
            if (!this.importId || this.importCompleted || this.importFailed) {
                return;
            }

            fetch('{{ route('customer.lists.subscribers.import.ajax.step', $list) }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ import_id: this.importId })
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    this.importFailed = true;
                    this.importError = 'Import failed.';
                    return;
                }

                const stats = data.import;
                if (!stats) {
                    this.queueStep();
                    return;
                }

                this.importPercent = stats.percent || 0;
                this.importText = new Intl.NumberFormat().format(stats.processed_count || 0) + ' / ' + new Intl.NumberFormat().format(stats.total_rows || 0) + ' processed';

                if (stats.status === 'failed') {
                    this.importFailed = true;
                    this.importError = stats.failure_reason || 'Import failed.';
                    return;
                }

                if (stats.status === 'completed') {
                    this.importCompleted = true;
                    this.importSuccess = 'Import completed: ' + new Intl.NumberFormat().format(stats.imported_count || 0) + ' imported, ' + new Intl.NumberFormat().format(stats.updated_count || 0) + ' updated, ' + new Intl.NumberFormat().format(stats.skipped_count || 0) + ' skipped, ' + new Intl.NumberFormat().format(stats.error_count || 0) + ' errors.';
                    return;
                }

                this.queueStep();
            })
            .catch(() => {
                this.queueStep();
            });
        }
    }
}
</script>
@endsection

