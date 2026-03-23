<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\SendingDomain;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendingDomainService
{
    /**
     * Get paginated list of sending domains for a customer.
     */
    public function getPaginated(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $mustAddOwn = (bool) $customer->groupSetting('domains.sending_domains.must_add', false);
        $canUseSystem = (bool) $customer->groupSetting('servers.permissions.can_use_system_servers', false);

        $query = SendingDomain::query()
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
     * Create a new sending domain.
     */
    public function create(?Customer $customer, array $data): SendingDomain
    {
        $domain = $data['domain'];

        $existing = SendingDomain::withTrashed()
            ->where('domain', $domain)
            ->first();

        if ($existing && !$existing->trashed()) {
            throw new \RuntimeException('This domain has already been added.');
        }

        // Generate DKIM keys
        $dkimPrivateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        
        if (!$dkimPrivateKey) {
            throw new \RuntimeException('Failed to generate DKIM keys. Please ensure OpenSSL is properly configured.');
        }
        
        openssl_pkey_export($dkimPrivateKey, $privateKey);
        $keyDetails = openssl_pkey_get_details($dkimPrivateKey);
        $publicKey = $keyDetails['key'] ?? null;
        
        if (!$publicKey) {
            throw new \RuntimeException('Failed to extract DKIM public key.');
        }
        
        // Extract public key for DNS (remove headers and newlines)
        $publicKeyForDns = str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n", "\r"], '', $publicKey);
        $publicKeyForDns = trim($publicKeyForDns);
        
        // Generate DKIM DNS record
        $dkimSelector = 'mail';
        $dkimDnsRecord = "v=DKIM1; k=rsa; p={$publicKeyForDns}";

        $spfRecord = $data['spf_record'] ?? 'v=spf1 a mx ~all';
        $dmarcRecord = $data['dmarc_record'] ?? "v=DMARC1; p=none; rua=mailto:dmarc@{$domain}; fo=1";

        $payload = [
            'customer_id' => $customer?->id,
            'domain' => $domain,
            'is_primary' => false,
            'status' => 'pending',
            'verification_token' => Str::random(32),
            'verified_at' => null,
            'spf_record' => $spfRecord,
            'dkim_public_key' => $publicKey,
            'dkim_private_key' => $privateKey,
            'dmarc_record' => $dmarcRecord,
            'dns_records' => [
                'dkim' => [
                    'selector' => $dkimSelector,
                    'public_key' => $publicKeyForDns,
                    'record' => $dkimDnsRecord,
                    'host' => "{$dkimSelector}._domainkey",
                ],
                'spf' => [
                    'host' => '@',
                    'record' => $spfRecord,
                ],
                'dmarc' => [
                    'host' => '_dmarc',
                    'record' => $dmarcRecord,
                ],
            ],
            'verification_data' => null,
            'notes' => $data['notes'] ?? null,
        ];

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update($payload);
            return $existing->fresh();
        }

        return SendingDomain::create($payload);
    }

    public function setPrimary(SendingDomain $sendingDomain): SendingDomain
    {
        return DB::transaction(function () use ($sendingDomain) {
            $customerId = $sendingDomain->customer_id;

            SendingDomain::query()
                ->where('customer_id', $customerId)
                ->where('id', '!=', $sendingDomain->id)
                ->update(['is_primary' => false]);

            $sendingDomain->update(['is_primary' => true]);

            return $sendingDomain->fresh();
        });
    }

    /**
     * Update an existing sending domain.
     */
    public function update(SendingDomain $sendingDomain, array $data): SendingDomain
    {
        if (array_key_exists('spf_record', $data) || array_key_exists('dmarc_record', $data)) {
            $dnsRecords = is_array($sendingDomain->dns_records) ? $sendingDomain->dns_records : [];

            if (array_key_exists('spf_record', $data)) {
                $dnsRecords['spf'] = array_merge($dnsRecords['spf'] ?? [], [
                    'host' => $dnsRecords['spf']['host'] ?? '@',
                    'record' => $data['spf_record'],
                ]);
            }

            if (array_key_exists('dmarc_record', $data)) {
                $dnsRecords['dmarc'] = array_merge($dnsRecords['dmarc'] ?? [], [
                    'host' => $dnsRecords['dmarc']['host'] ?? '_dmarc',
                    'record' => $data['dmarc_record'],
                ]);
            }

            $data['dns_records'] = $dnsRecords;
        }

        $sendingDomain->update($data);
        return $sendingDomain->fresh();
    }

    /**
     * Delete a sending domain.
     */
    public function delete(SendingDomain $sendingDomain): bool
    {
        return $sendingDomain->delete();
    }

    /**
     * Verify a sending domain by checking DNS records.
     */
    public function verify(SendingDomain $sendingDomain): array
    {
        $results = [
            'dkim' => false,
            'spf' => false,
            'dmarc' => false,
            'errors' => [],
        ];

        // Check DKIM record
        if ($sendingDomain->dkim_public_key && isset($sendingDomain->dns_records['dkim'])) {
            $dkimSelector = $sendingDomain->dns_records['dkim']['selector'] ?? 'mail';
            $dkimHost = "{$dkimSelector}._domainkey.{$sendingDomain->domain}";
            
            try {
                $dkimRecords = @dns_get_record($dkimHost, DNS_TXT);
                
                if ($dkimRecords === false || empty($dkimRecords)) {
                    $results['errors'][] = "DKIM record not found at {$dkimHost}. Please add the TXT record to your DNS.";
                } else {
                    // Extract public key from stored record
                    $expectedPublicKey = $sendingDomain->dns_records['dkim']['public_key'] ?? null;
                    
                    if ($expectedPublicKey) {
                        // Check if any DNS record contains the public key
                        foreach ($dkimRecords as $record) {
                            if (isset($record['txt'])) {
                                $txtRecord = $record['txt'];
                                // Check if record contains v=DKIM1 and the public key
                                if (strpos($txtRecord, 'v=DKIM1') !== false && strpos($txtRecord, $expectedPublicKey) !== false) {
                                    $results['dkim'] = true;
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (!$results['dkim']) {
                        $results['errors'][] = "DKIM record found at {$dkimHost} but public key doesn't match. Please verify the DNS record is correct.";
                    }
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Failed to check DKIM record: " . $e->getMessage();
            }
        } else {
            $results['errors'][] = "DKIM keys not generated. Please recreate the sending domain.";
        }

        // Check SPF record (optional but recommended)
        if ($sendingDomain->spf_record) {
            try {
                $spfRecords = dns_get_record($sendingDomain->domain, DNS_TXT);

                foreach ($spfRecords as $record) {
                    if (isset($record['txt']) && strpos($record['txt'], 'v=spf1') !== false) {
                        $results['spf'] = true;
                        break;
                    }
                }

                if (!$results['spf']) {
                    $results['errors'][] = 'SPF record not found. Please add the SPF TXT record to your DNS.';
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Failed to check SPF record: " . $e->getMessage();
            }
        } else {
            // SPF is optional, so mark as passed if not required
            $results['spf'] = true;
        }

        // Check DMARC record (optional)
        $dmarcHost = "_dmarc.{$sendingDomain->domain}";
        try {
            $dmarcRecords = dns_get_record($dmarcHost, DNS_TXT);
            
            foreach ($dmarcRecords as $record) {
                if (isset($record['txt']) && strpos($record['txt'], 'v=DMARC1') !== false) {
                    $results['dmarc'] = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            // DMARC is optional, so we don't add to errors
        }

        // Domain is verified only if DKIM is present (required)
        $isVerified = $results['dkim'];

        // Update domain status
        $sendingDomain->update([
            'status' => $isVerified ? 'verified' : 'pending',
            'verified_at' => $isVerified ? now() : null,
            'verification_data' => [
                'last_checked' => now()->toDateTimeString(),
                'dkim_verified' => $results['dkim'],
                'spf_verified' => $results['spf'],
                'dmarc_verified' => $results['dmarc'],
                'errors' => $results['errors'],
            ],
        ]);

        return $results;
    }
}

