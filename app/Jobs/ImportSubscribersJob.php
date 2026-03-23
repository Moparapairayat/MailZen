<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\SubscriberImport;
use App\Notifications\SubscriberImportStatusNotification;
use App\Services\ListSubscriberService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportSubscribersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public EmailList $emailList,
        public string $filePath,
        public array $columnMapping,
        public bool $skipDuplicates = true,
        public bool $updateExisting = false,
        public string $source = 'import',
        public ?string $ipAddress = null,
        public ?int $subscriberImportId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $listSubscriberService = app(ListSubscriberService::class);

        $subscriberImport = null;
        if ($this->subscriberImportId) {
            $subscriberImport = SubscriberImport::query()->find($this->subscriberImportId);
        }

        // Verify file exists
        if (!file_exists($this->filePath)) {
            Log::error("Import file not found: {$this->filePath}");
            throw new \RuntimeException("Import file not found: {$this->filePath}");
        }

        // Verify file is readable
        if (!is_readable($this->filePath)) {
            Log::error("Import file is not readable: {$this->filePath}");
            throw new \RuntimeException("Import file is not readable: {$this->filePath}");
        }

        $file = fopen($this->filePath, 'r');
        
        if ($file === false) {
            Log::error("Failed to open import file: {$this->filePath}");
            throw new \RuntimeException("Failed to open import file: {$this->filePath}");
        }

        $headers = fgetcsv($file);
        
        if ($headers === false || empty($headers)) {
            fclose($file);
            Log::error("Invalid CSV file: No headers found in {$this->filePath}");
            throw new \RuntimeException("Invalid CSV file: No headers found");
        }

        $headers = array_map(fn ($header) => $this->cleanHeader($header), $headers);

        $totalRows = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }
            $totalRows++;
        }

        rewind($file);
        $headers = fgetcsv($file);

        $headers = $headers === false ? [] : array_map(fn ($header) => $this->cleanHeader($header), $headers);

        $headerByNormalized = [];
        foreach ($headers as $header) {
            $headerByNormalized[$this->normalizeHeader($header)] = $header;
        }

        $emailColumn = $this->resolveMappedColumn($this->columnMapping['email'] ?? null, $headerByNormalized);
        $firstNameColumn = $this->resolveMappedColumn($this->columnMapping['first_name'] ?? null, $headerByNormalized);
        $lastNameColumn = $this->resolveMappedColumn($this->columnMapping['last_name'] ?? null, $headerByNormalized);
        $nameColumn = null;
        if (!$firstNameColumn && !$lastNameColumn) {
            $nameColumn = $this->firstExistingColumnByNormalized($headerByNormalized, ['name', 'full name', 'fullname']);
        }

        $nameColumnFromMapping = null;
        if ($firstNameColumn && !$lastNameColumn) {
            $firstNameNormalized = $this->normalizeHeader($firstNameColumn);
            if (in_array($firstNameNormalized, ['name', 'full name', 'fullname'], true)) {
                $nameColumnFromMapping = $firstNameColumn;
            }
        }

        $resolvedNameColumn = $nameColumnFromMapping ?? $nameColumn;

        $this->maybeAddListCustomFields(
            $this->emailList,
            is_array($this->columnMapping) ? $this->columnMapping : [],
            $headers,
            $headerByNormalized,
            $emailColumn,
            $firstNameColumn,
            $lastNameColumn,
            $resolvedNameColumn
        );

        if ($subscriberImport) {
            $subscriberImport->update([
                'status' => 'running',
                'total_rows' => (int) $totalRows,
                'processed_count' => 0,
                'imported_count' => 0,
                'updated_count' => 0,
                'skipped_count' => 0,
                'error_count' => 0,
                'failure_reason' => null,
                'started_at' => now(),
                'finished_at' => null,
            ]);
        }

        $processed = 0;
        $imported = 0;
        $skipped = 0;
        $updated = 0;
        $errors = 0;

        while (($row = fgetcsv($file)) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $processed++;

            // Ensure row has same number of columns as headers
            if (count($row) !== count($headers)) {
                $skipped++;
                $this->maybePersistProgress($subscriberImport, $processed, $imported, $updated, $skipped, $errors);
                continue;
            }

            $data = array_combine($headers, $row);
            
            if (!$emailColumn || !isset($data[$emailColumn])) {
                $skipped++;
                $this->maybePersistProgress($subscriberImport, $processed, $imported, $updated, $skipped, $errors);
                continue;
            }

            $email = trim($data[$emailColumn]);
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                $this->maybePersistProgress($subscriberImport, $processed, $imported, $updated, $skipped, $errors);
                continue;
            }

            $subscriberData = [
                'list_id' => $this->emailList->id,
                'email' => strtolower(trim($email)),
                'first_name' => $firstNameColumn && isset($data[$firstNameColumn]) ? trim((string) $data[$firstNameColumn]) : null,
                'last_name' => $lastNameColumn && isset($data[$lastNameColumn]) ? trim((string) $data[$lastNameColumn]) : null,
                'source' => $this->source,
                'ip_address' => $this->ipAddress,
                'subscribed_at' => now(),
                'custom_fields' => $this->extractCustomFields(
                    $data,
                    $headerByNormalized,
                    $emailColumn,
                    $firstNameColumn,
                    $lastNameColumn,
                    $resolvedNameColumn
                ),
            ];

            $shouldSplitFullName = false;
            if ($resolvedNameColumn && isset($data[$resolvedNameColumn])) {
                if ($resolvedNameColumn === $firstNameColumn && !$lastNameColumn) {
                    $shouldSplitFullName = true;
                }

                if (($subscriberData['first_name'] === null || $subscriberData['first_name'] === '') && ($subscriberData['last_name'] === null || $subscriberData['last_name'] === '')) {
                    $shouldSplitFullName = true;
                }
            }

            if ($shouldSplitFullName) {
                [$first, $last] = $this->splitFullName((string) $data[$resolvedNameColumn]);
                $subscriberData['first_name'] = $first !== '' ? $first : null;
                $subscriberData['last_name'] = $last !== '' ? $last : null;
            }

            // Check if subscriber exists
            $existing = ListSubscriber::where('list_id', $this->emailList->id)
                ->where('email', $subscriberData['email'])
                ->first();

            if ($existing) {
                if ($this->skipDuplicates && !$this->updateExisting) {
                    $skipped++;
                    $this->maybePersistProgress($subscriberImport, $processed, $imported, $updated, $skipped, $errors);
                    continue;
                }

                if ($this->updateExisting) {
                    $existingCustomFields = is_array($existing->custom_fields) ? $existing->custom_fields : [];
                    $importCustomFields = is_array($subscriberData['custom_fields'] ?? null) ? ($subscriberData['custom_fields'] ?? []) : [];
                    $subscriberData['custom_fields'] = array_merge($existingCustomFields, $importCustomFields);
                    $existing->update($subscriberData);
                    $updated++;
                }
            } else {
                try {
                    // Create through service so confirmations and autoresponder triggers run.
                    // ListSubscriberService will handle double opt-in status.
                    $listSubscriberService->create($this->emailList, $subscriberData);
                    $imported++;
                } catch (\Throwable $e) {
                    $errors++;
                    Log::warning('Failed to import subscriber row', [
                        'list_id' => $this->emailList->id,
                        'email' => $subscriberData['email'] ?? null,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            $this->maybePersistProgress($subscriberImport, $processed, $imported, $updated, $skipped, $errors);
        }

        fclose($file);

        // Update list subscriber counts
        try {
            app(\App\Services\EmailListService::class)->updateSubscriberCounts($this->emailList);
        } catch (\Exception $e) {
            Log::warning("Failed to update subscriber counts: " . $e->getMessage());
        }

        // Delete the temporary file
        if (file_exists($this->filePath)) {
            try {
                unlink($this->filePath);
            } catch (\Exception $e) {
                Log::warning("Failed to delete import file: " . $e->getMessage());
            }
        }

        if ($subscriberImport) {
            $subscriberImport->update([
                'status' => 'completed',
                'processed_count' => (int) $processed,
                'imported_count' => (int) $imported,
                'updated_count' => (int) $updated,
                'skipped_count' => (int) $skipped,
                'error_count' => (int) $errors,
                'finished_at' => now(),
            ]);

            $this->notifyCustomer($subscriberImport);
        }

        Log::info("Import completed for list {$this->emailList->id}: {$imported} imported, {$updated} updated, {$skipped} skipped, {$errors} errors");
    }

    private function maybePersistProgress(
        ?SubscriberImport $subscriberImport,
        int $processed,
        int $imported,
        int $updated,
        int $skipped,
        int $errors
    ): void {
        if (!$subscriberImport) {
            return;
        }

        if ($processed % 25 !== 0) {
            return;
        }

        $subscriberImport->update([
            'processed_count' => (int) $processed,
            'imported_count' => (int) $imported,
            'updated_count' => (int) $updated,
            'skipped_count' => (int) $skipped,
            'error_count' => (int) $errors,
        ]);
    }

    private function notifyCustomer(SubscriberImport $subscriberImport): void
    {
        $customer = Customer::query()->find($subscriberImport->customer_id);
        if (!$customer) {
            return;
        }

        $customer->notify(new SubscriberImportStatusNotification($subscriberImport));
    }

    private function cleanHeader(mixed $header): string
    {
        $value = trim((string) $header);

        $withoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        if (is_string($withoutBom)) {
            $value = $withoutBom;
        }

        return trim($value);
    }

    private function normalizeHeader(mixed $header): string
    {
        $value = $this->cleanHeader($header);
        $value = preg_replace('/\s+/', ' ', $value);
        if (!is_string($value)) {
            $value = (string) $value;
        }

        return Str::lower(trim($value));
    }

    private function resolveMappedColumn(?string $mapped, array $headerByNormalized): ?string
    {
        $mapped = $mapped !== null ? trim($mapped) : null;
        if ($mapped === null || $mapped === '') {
            return null;
        }

        $normalized = $this->normalizeHeader($mapped);
        if (isset($headerByNormalized[$normalized])) {
            return $headerByNormalized[$normalized];
        }

        return $this->cleanHeader($mapped);
    }

    private function firstExistingColumnByNormalized(array $headerByNormalized, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeHeader($candidate);
            if (isset($headerByNormalized[$normalized])) {
                return $headerByNormalized[$normalized];
            }
        }

        return null;
    }

    private function splitFullName(string $fullName): array
    {
        $fullName = trim(preg_replace('/\s+/', ' ', $fullName) ?? '');
        if ($fullName === '') {
            return ['', ''];
        }

        $parts = explode(' ', $fullName);
        if (count($parts) === 1) {
            return [$parts[0], ''];
        }

        $first = array_shift($parts);
        $last = implode(' ', $parts);

        return [trim($first), trim($last)];
    }

    private function extractCustomFields(
        array $rowData,
        array $headerByNormalized,
        ?string $emailColumn,
        ?string $firstNameColumn,
        ?string $lastNameColumn,
        ?string $resolvedNameColumn
    ): array {
        $result = [];

        $mapping = is_array($this->columnMapping) ? $this->columnMapping : [];
        $mappedCustomFields = isset($mapping['custom_fields']) && is_array($mapping['custom_fields']) ? $mapping['custom_fields'] : [];

        foreach ($mappedCustomFields as $key => $mappedColumn) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $key)) {
                $key = $this->normalizeCustomFieldKey($key);
            }

            $resolved = $this->resolveMappedColumn(is_string($mappedColumn) ? $mappedColumn : null, $headerByNormalized);
            if (!$resolved || !array_key_exists($resolved, $rowData)) {
                continue;
            }

            $value = trim((string) ($rowData[$resolved] ?? ''));
            if ($value === '') {
                continue;
            }

            $result[$key] = $value;
        }

        $captureUnmapped = isset($mapping['capture_unmapped']) ? (bool) $mapping['capture_unmapped'] : true;
        if (!$captureUnmapped) {
            return $result;
        }

        $usedColumns = array_filter([
            $emailColumn,
            $firstNameColumn,
            $lastNameColumn,
            $resolvedNameColumn,
        ], fn ($v) => is_string($v) && $v !== '');

        foreach ($mappedCustomFields as $mappedColumn) {
            if (!is_string($mappedColumn)) {
                continue;
            }
            $resolved = $this->resolveMappedColumn($mappedColumn, $headerByNormalized);
            if (is_string($resolved) && $resolved !== '') {
                $usedColumns[] = $resolved;
            }
        }

        $usedColumns = array_values(array_unique($usedColumns));

        foreach ($rowData as $header => $value) {
            if (!is_string($header) || $header === '') {
                continue;
            }

            if (in_array($header, $usedColumns, true)) {
                continue;
            }

            $v = trim((string) ($value ?? ''));
            if ($v === '') {
                continue;
            }

            $key = $this->normalizeCustomFieldKey($header);
            if ($key === '') {
                continue;
            }

            if (array_key_exists($key, $result)) {
                continue;
            }

            $result[$key] = $v;
        }

        return $result;
    }

    private function normalizeCustomFieldKey(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = Str::lower($value);
        $value = preg_replace('/[^a-z0-9_]+/i', '_', $value) ?? '';
        $value = preg_replace('/_+/', '_', $value) ?? '';
        $value = trim($value, '_');

        if ($value === '') {
            return '';
        }

        if (!preg_match('/^[a-z]/', $value)) {
            $value = 'f_' . $value;
        }

        $value = preg_replace('/[^a-z0-9_]+/i', '', $value) ?? '';

        return $value;
    }

    private function maybeAddListCustomFields(
        EmailList $emailList,
        array $mapping,
        array $headers,
        array $headerByNormalized,
        ?string $emailColumn,
        ?string $firstNameColumn,
        ?string $lastNameColumn,
        ?string $resolvedNameColumn
    ): void {
        $shouldAdd = isset($mapping['add_list_custom_fields']) ? (bool) $mapping['add_list_custom_fields'] : false;
        if (!$shouldAdd) {
            return;
        }

        $existing = is_array($emailList->custom_fields) ? $emailList->custom_fields : [];
        $existingByKey = [];
        foreach ($existing as $def) {
            if (!is_array($def)) {
                continue;
            }
            $k = trim((string) ($def['key'] ?? ''));
            if ($k === '') {
                continue;
            }
            $existingByKey[strtolower($k)] = true;
        }

        $reserved = ['email', 'first_name', 'last_name', 'name', 'full_name'];
        foreach ($reserved as $r) {
            $existingByKey[strtolower($r)] = true;
        }

        $toAdd = [];
        $mappedCustomFields = isset($mapping['custom_fields']) && is_array($mapping['custom_fields']) ? $mapping['custom_fields'] : [];

        foreach ($mappedCustomFields as $key => $mappedColumn) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $key)) {
                $key = $this->normalizeCustomFieldKey($key);
            }
            if ($key === '') {
                continue;
            }

            $keyLower = strtolower($key);
            if (isset($existingByKey[$keyLower])) {
                continue;
            }

            $label = $key;
            $resolved = $this->resolveMappedColumn(is_string($mappedColumn) ? $mappedColumn : null, $headerByNormalized);
            if (is_string($resolved) && $resolved !== '') {
                $label = trim($resolved) !== '' ? trim($resolved) : $label;
            }

            $toAdd[] = [
                'key' => $key,
                'label' => $label,
                'type' => 'text',
                'required' => false,
            ];
            $existingByKey[$keyLower] = true;
        }

        $captureUnmapped = isset($mapping['capture_unmapped']) ? (bool) $mapping['capture_unmapped'] : true;
        if ($captureUnmapped) {
            $usedColumns = array_filter([
                $emailColumn,
                $firstNameColumn,
                $lastNameColumn,
                $resolvedNameColumn,
            ], fn ($v) => is_string($v) && $v !== '');

            foreach ($mappedCustomFields as $mappedColumn) {
                if (!is_string($mappedColumn)) {
                    continue;
                }
                $resolved = $this->resolveMappedColumn($mappedColumn, $headerByNormalized);
                if (is_string($resolved) && $resolved !== '') {
                    $usedColumns[] = $resolved;
                }
            }

            $usedColumns = array_values(array_unique($usedColumns));

            foreach ($headers as $header) {
                $header = is_string($header) ? trim($header) : '';
                if ($header === '' || in_array($header, $usedColumns, true)) {
                    continue;
                }

                $key = $this->normalizeCustomFieldKey($header);
                if ($key === '') {
                    continue;
                }

                $keyLower = strtolower($key);
                if (isset($existingByKey[$keyLower])) {
                    continue;
                }

                $toAdd[] = [
                    'key' => $key,
                    'label' => $header,
                    'type' => 'text',
                    'required' => false,
                ];
                $existingByKey[$keyLower] = true;
            }
        }

        if (empty($toAdd)) {
            return;
        }

        $emailList->update([
            'custom_fields' => array_values(array_merge($existing, $toAdd)),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ImportSubscribersJob failed for list {$this->emailList->id}: " . $exception->getMessage());

        if ($this->subscriberImportId) {
            $subscriberImport = SubscriberImport::query()->find($this->subscriberImportId);
            if ($subscriberImport) {
                $subscriberImport->update([
                    'status' => 'failed',
                    'failure_reason' => $exception->getMessage(),
                    'finished_at' => now(),
                ]);

                $this->notifyCustomer($subscriberImport);
            }
        }
        
        // Try to delete the file even on failure
        if (file_exists($this->filePath)) {
            try {
                unlink($this->filePath);
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }
    }
}
