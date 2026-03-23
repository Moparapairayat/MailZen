<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use App\Models\SubscriberImport;
use App\Notifications\SubscriberImportStatusNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubscriberImportProcessor
{
    public function processDueImports(int $limit = 3, int $rowsPerImport = 2000, int $maxSeconds = 45): int
    {
        $startedAt = microtime(true);

        $imports = SubscriberImport::query()
            ->whereIn('status', ['queued', 'running'])
            ->where(function ($q) {
                $q->whereNull('locked_at')
                    ->orWhere('locked_at', '<=', now()->subMinutes(10));
            })
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($imports->isEmpty()) {
            return 0;
        }

        $processed = 0;

        foreach ($imports as $import) {
            if ((microtime(true) - $startedAt) >= $maxSeconds) {
                break;
            }

            if (!$this->claimImport($import)) {
                continue;
            }

            try {
                $this->processImport($import->fresh(), $rowsPerImport, $startedAt, $maxSeconds);
                $processed++;
            } catch (\Throwable $e) {
                Log::error('Subscriber import processor failed', [
                    'subscriber_import_id' => $import->id,
                    'error' => $e->getMessage(),
                ]);

                $this->markFailed($import->fresh(), $e);
            } finally {
                $this->releaseImport($import->fresh());
            }
        }

        return $processed;
    }

    public function processImportNow(SubscriberImport $import, int $rows = 500, int $maxSeconds = 8): void
    {
        if (!in_array((string) $import->status, ['queued', 'running'], true)) {
            return;
        }

        if (!$this->claimImport($import)) {
            return;
        }

        try {
            $startedAt = microtime(true);
            $this->processImport($import->fresh(), $rows, $startedAt, $maxSeconds);
        } catch (\Throwable $e) {
            Log::error('Subscriber import immediate processing failed', [
                'subscriber_import_id' => $import->id,
                'error' => $e->getMessage(),
            ]);

            $this->markFailed($import->fresh(), $e);
        } finally {
            $this->releaseImport($import->fresh());
        }
    }

    private function claimImport(SubscriberImport $import): bool
    {
        $claimed = SubscriberImport::query()
            ->whereKey($import->id)
            ->whereIn('status', ['queued', 'running'])
            ->where(function ($q) {
                $q->whereNull('locked_at')
                    ->orWhere('locked_at', '<=', now()->subMinutes(10));
            })
            ->update([
                'locked_at' => now(),
            ]);

        return $claimed === 1;
    }

    private function releaseImport(?SubscriberImport $import): void
    {
        if (!$import) {
            return;
        }

        if (in_array((string) $import->status, ['queued', 'running'], true)) {
            $import->update([
                'locked_at' => null,
            ]);
        }
    }

    private function processImport(SubscriberImport $import, int $maxRows, float $globalStartedAt, int $maxSeconds): void
    {
        $emailList = EmailList::query()->find($import->list_id);
        if (!$emailList) {
            throw new \RuntimeException('Email list not found.');
        }

        $storedPath = (string) ($import->stored_path ?? '');
        if ($storedPath === '') {
            throw new \RuntimeException('Import file not found.');
        }

        $filePath = null;
        $relativePath = null;

        if (Storage::disk('local')->exists($storedPath)) {
            $relativePath = $storedPath;
            $filePath = Storage::disk('local')->path($storedPath);
        } elseif (is_string($storedPath) && is_file($storedPath)) {
            $filePath = $storedPath;
        }

        if (!$filePath) {
            Log::warning('Subscriber import file missing', [
                'subscriber_import_id' => $import->id,
                'stored_path' => $storedPath,
            ]);

            throw new \RuntimeException('Import file not found.');
        }

        if (!is_readable($filePath)) {
            throw new \RuntimeException('Import file is not readable.');
        }

        $file = fopen($filePath, 'r');
        if ($file === false) {
            throw new \RuntimeException('Failed to open import file.');
        }

        try {
            if ($import->started_at === null) {
                $import->update([
                    'status' => 'running',
                    'started_at' => now(),
                    'failure_reason' => null,
                ]);
            } elseif ((string) $import->status !== 'running') {
                $import->update([
                    'status' => 'running',
                    'failure_reason' => null,
                ]);
            }

            $headers = is_array($import->headers) ? $import->headers : [];
            $offset = (int) ($import->file_offset ?? 0);

            if (empty($headers) || $offset <= 0) {
                $rawHeaders = fgetcsv($file);
                if ($rawHeaders === false || empty($rawHeaders)) {
                    throw new \RuntimeException('Invalid CSV file: No headers found.');
                }

                $headers = array_map(fn ($h) => $this->cleanHeader($h), $rawHeaders);
                $afterHeaderPos = ftell($file);

                $totalRows = (int) ($import->total_rows ?? 0);
                if ($totalRows <= 0) {
                    $totalRows = 0;
                    while (($row = fgetcsv($file)) !== false) {
                        if (empty(array_filter($row))) {
                            continue;
                        }
                        $totalRows++;
                    }
                }

                $import->update([
                    'headers' => $headers,
                    'total_rows' => $totalRows,
                    'file_offset' => (int) $afterHeaderPos,
                ]);

                $offset = (int) $afterHeaderPos;
            }

            $headerByNormalized = [];
            foreach ($headers as $header) {
                $headerByNormalized[$this->normalizeHeader($header)] = $header;
            }

            $mapping = is_array($import->column_mapping) ? $import->column_mapping : [];
            $emailColumn = $this->resolveMappedColumn($mapping['email'] ?? null, $headerByNormalized);
            $firstNameColumn = $this->resolveMappedColumn($mapping['first_name'] ?? null, $headerByNormalized);
            $lastNameColumn = $this->resolveMappedColumn($mapping['last_name'] ?? null, $headerByNormalized);

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
                $emailList,
                $mapping,
                $headers,
                $headerByNormalized,
                $emailColumn,
                $firstNameColumn,
                $lastNameColumn,
                $resolvedNameColumn
            );

            if ($offset > 0) {
                fseek($file, $offset);
            }

            $processed = (int) ($import->processed_count ?? 0);
            $imported = (int) ($import->imported_count ?? 0);
            $updated = (int) ($import->updated_count ?? 0);
            $skipped = (int) ($import->skipped_count ?? 0);
            $errors = (int) ($import->error_count ?? 0);

            $skipDuplicates = (bool) ($import->skip_duplicates ?? true);
            $updateExisting = (bool) ($import->update_existing ?? false);

            $listSubscriberService = app(ListSubscriberService::class);

            $batchProcessed = 0;
            $persistEvery = 200;

            while ($batchProcessed < $maxRows && (microtime(true) - $globalStartedAt) < ($maxSeconds - 1)) {
                $row = fgetcsv($file);
                if ($row === false) {
                    break;
                }

                $offset = ftell($file);

                if (empty(array_filter($row))) {
                    continue;
                }

                $processed++;
                $batchProcessed++;

                if (count($row) !== count($headers)) {
                    $skipped++;
                    $this->maybePersist($import, $processed, $imported, $updated, $skipped, $errors, $offset, $persistEvery);
                    continue;
                }

                $data = array_combine($headers, $row);
                if (!$emailColumn || !isset($data[$emailColumn])) {
                    $skipped++;
                    $this->maybePersist($import, $processed, $imported, $updated, $skipped, $errors, $offset, $persistEvery);
                    continue;
                }

                $email = trim((string) $data[$emailColumn]);
                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped++;
                    $this->maybePersist($import, $processed, $imported, $updated, $skipped, $errors, $offset, $persistEvery);
                    continue;
                }

                $subscriberData = [
                    'list_id' => $emailList->id,
                    'email' => strtolower(trim($email)),
                    'first_name' => $firstNameColumn && isset($data[$firstNameColumn]) ? trim((string) $data[$firstNameColumn]) : null,
                    'last_name' => $lastNameColumn && isset($data[$lastNameColumn]) ? trim((string) $data[$lastNameColumn]) : null,
                    'source' => (string) ($import->source ?? 'csv_import'),
                    'ip_address' => (string) ($import->ip_address ?? ''),
                    'subscribed_at' => now(),
                    'custom_fields' => $this->extractCustomFields(
                        $data,
                        $headerByNormalized,
                        $mapping,
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

                $existing = ListSubscriber::query()
                    ->where('list_id', $emailList->id)
                    ->where('email', $subscriberData['email'])
                    ->first();

                if ($existing) {
                    if ($skipDuplicates && !$updateExisting) {
                        $skipped++;
                        $this->maybePersist($import, $processed, $imported, $updated, $skipped, $errors, $offset, $persistEvery);
                        continue;
                    }

                    if ($updateExisting) {
                        $existingCustomFields = is_array($existing->custom_fields) ? $existing->custom_fields : [];
                        $importCustomFields = is_array($subscriberData['custom_fields'] ?? null) ? ($subscriberData['custom_fields'] ?? []) : [];
                        $subscriberData['custom_fields'] = array_merge($existingCustomFields, $importCustomFields);
                        $existing->update($subscriberData);
                        $updated++;
                    }
                } else {
                    try {
                        $listSubscriberService->create($emailList, $subscriberData);
                        $imported++;
                    } catch (\Throwable $e) {
                        $errors++;
                        Log::warning('Failed to import subscriber row', [
                            'subscriber_import_id' => $import->id,
                            'list_id' => $emailList->id,
                            'email' => $subscriberData['email'] ?? null,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }

                $this->maybePersist($import, $processed, $imported, $updated, $skipped, $errors, $offset, $persistEvery);
            }

            $isFinished = feof($file);

            $import->update([
                'processed_count' => (int) $processed,
                'imported_count' => (int) $imported,
                'updated_count' => (int) $updated,
                'skipped_count' => (int) $skipped,
                'error_count' => (int) $errors,
                'file_offset' => (int) $offset,
            ]);

            if ($isFinished) {
                try {
                    app(EmailListService::class)->updateSubscriberCounts($emailList);
                } catch (\Throwable $e) {
                    Log::warning('Failed to update subscriber counts after import', [
                        'subscriber_import_id' => $import->id,
                        'list_id' => $emailList->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $import->update([
                    'status' => 'completed',
                    'finished_at' => now(),
                    'locked_at' => null,
                ]);

                $this->notifyCustomer($import->fresh());

                try {
                    Storage::disk('local')->delete($relativePath);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete import file after completion', [
                        'subscriber_import_id' => $import->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } finally {
            fclose($file);
        }
    }

    private function maybePersist(
        SubscriberImport $import,
        int $processed,
        int $imported,
        int $updated,
        int $skipped,
        int $errors,
        int $fileOffset,
        int $persistEvery
    ): void {
        if ($processed % $persistEvery !== 0) {
            return;
        }

        $import->update([
            'processed_count' => (int) $processed,
            'imported_count' => (int) $imported,
            'updated_count' => (int) $updated,
            'skipped_count' => (int) $skipped,
            'error_count' => (int) $errors,
            'file_offset' => (int) $fileOffset,
        ]);
    }

    private function markFailed(SubscriberImport $import, \Throwable $exception): void
    {
        if ((string) $import->status === 'completed') {
            return;
        }

        $import->update([
            'status' => 'failed',
            'failure_reason' => $exception->getMessage(),
            'finished_at' => now(),
            'locked_at' => null,
        ]);

        $this->notifyCustomer($import->fresh());
    }

    private function notifyCustomer(?SubscriberImport $import): void
    {
        if (!$import) {
            return;
        }

        $customer = Customer::query()->find($import->customer_id);
        if (!$customer) {
            return;
        }

        $customer->notify(new SubscriberImportStatusNotification($import));
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
        array $mapping,
        ?string $emailColumn,
        ?string $firstNameColumn,
        ?string $lastNameColumn,
        ?string $resolvedNameColumn
    ): array {
        $result = [];

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
}
