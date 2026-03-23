<?php

namespace App\Services;

use App\Models\TrackingDomain;
use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TrackingDomainService
{
    protected function expectedCnameTarget(): string
    {
        $configured = (string) config('mailzen.tracking_domains.cname_target', '');
        $configured = strtolower(trim($configured));
        $configured = rtrim($configured, '.');

        if ($configured !== '') {
            return $configured;
        }

        $appUrl = (string) config('app.url');
        $host = parse_url($appUrl, PHP_URL_HOST);
        $host = is_string($host) ? strtolower(trim($host)) : '';
        $host = rtrim($host, '.');

        return $host;
    }

    protected function normalizeDnsTarget(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        return rtrim($value, '.');
    }

    /**
     * Get paginated list of tracking domains for a customer.
     */
    public function getPaginated(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $mustAddOwn = (bool) $customer->groupSetting('domains.tracking_domains.must_add', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $query = TrackingDomain::query()
            ->when($mustAddOwn, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer, $canUseSystem) {
                $q->where(function ($sub) use ($customer, $canUseSystem) {
                    $sub->where('customer_id', $customer->id);
                    if ($canUseSystem) {
                        $sub->orWhereNull('customer_id');
                    }
                });
            });

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new tracking domain.
     */
    public function create(Customer $customer, array $data): TrackingDomain
    {
        $domain = strtolower(trim($data['domain']));

        $expectedTarget = $this->expectedCnameTarget();
        $dnsRecords = [
            'cname' => [
                'type' => 'CNAME',
                'host' => $domain,
                'target' => $expectedTarget,
            ],
        ];

        $existing = TrackingDomain::withTrashed()
            ->where('domain', $domain)
            ->first();

        if ($existing !== null) {
            if ($existing->trashed() && (int) $existing->customer_id === (int) $customer->id) {
                $existing->restore();

                $existing->update([
                    'customer_id' => $customer->id,
                    'status' => 'pending',
                    'verification_token' => Str::random(32),
                    'dns_records' => $dnsRecords,
                    'notes' => $data['notes'] ?? null,
                    'verified_at' => null,
                    'verification_data' => null,
                ]);

                return $existing->fresh();
            }

            throw ValidationException::withMessages([
                'domain' => 'This domain has already been added.',
            ]);
        }

        return TrackingDomain::create([
            'customer_id' => $customer->id,
            'domain' => $domain,
            'status' => 'pending',
            'verification_token' => Str::random(32),
            'dns_records' => $dnsRecords,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Update an existing tracking domain.
     */
    public function update(TrackingDomain $trackingDomain, array $data): TrackingDomain
    {
        $trackingDomain->update($data);
        return $trackingDomain->fresh();
    }

    /**
     * Delete a tracking domain.
     */
    public function delete(TrackingDomain $trackingDomain): bool
    {
        return $trackingDomain->delete();
    }

    /**
     * Verify a tracking domain.
     */
    public function verify(TrackingDomain $trackingDomain): array
    {
        $expectedTarget = $this->expectedCnameTarget();

        $dnsRecords = is_array($trackingDomain->dns_records) ? $trackingDomain->dns_records : [];
        if (!isset($dnsRecords['cname']) || !is_array($dnsRecords['cname'])) {
            $dnsRecords['cname'] = [
                'type' => 'CNAME',
                'host' => $trackingDomain->domain,
                'target' => $expectedTarget,
            ];
        }

        $expectedTarget = $this->normalizeDnsTarget($dnsRecords['cname']['target'] ?? $expectedTarget);

        $results = [
            'cname' => false,
            'expected_target' => $expectedTarget,
            'found_targets' => [],
            'errors' => [],
        ];

        try {
            $cnameRecords = @dns_get_record($trackingDomain->domain, DNS_CNAME);

            if ($cnameRecords === false || empty($cnameRecords)) {
                $results['errors'][] = "CNAME record not found for {$trackingDomain->domain}. Please add the CNAME record and try again.";
            } else {
                $targets = [];
                foreach ($cnameRecords as $record) {
                    if (isset($record['target'])) {
                        $targets[] = $this->normalizeDnsTarget($record['target']);
                    }
                }

                $targets = array_values(array_unique(array_filter($targets)));
                $results['found_targets'] = $targets;

                if (in_array($expectedTarget, $targets, true)) {
                    $results['cname'] = true;
                } else {
                    $foundText = empty($targets) ? 'none' : implode(', ', $targets);
                    $results['errors'][] = "CNAME record found but does not match expected target. Expected: {$expectedTarget}. Found: {$foundText}.";
                }
            }
        } catch (\Throwable $e) {
            $results['errors'][] = 'Failed to check CNAME record: ' . $e->getMessage();
        }

        $isVerified = (bool) $results['cname'];

        $trackingDomain->update([
            'status' => $isVerified ? 'verified' : 'pending',
            'verified_at' => $isVerified ? now() : null,
            'dns_records' => $dnsRecords,
            'verification_data' => [
                'last_checked' => now()->toDateTimeString(),
                'cname_verified' => $results['cname'],
                'expected_target' => $expectedTarget,
                'found_targets' => $results['found_targets'],
                'errors' => $results['errors'],
            ],
        ]);

        return $results;
    }
}

