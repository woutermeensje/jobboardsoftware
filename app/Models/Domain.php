<?php

namespace App\Models;

use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

/** @use HasFactory<DomainFactory> */
class Domain extends BaseDomain
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_FAILED = 'failed';

    public const SSL_PENDING = 'pending';

    public const SSL_ACTIVE = 'active';

    public const SSL_FAILED = 'failed';

    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
        'ssl_issued_at' => 'datetime',
        'verification_payload' => 'array',
    ];

    public function isReadyForTraffic(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ssl_status === self::SSL_ACTIVE;
    }

    /**
     * Look up the DNS TXT/CNAME records for this domain and update its
     * verification status accordingly. Returns whether verification succeeded.
     */
    public function checkDnsVerification(): bool
    {
        $payload = $this->verification_payload ?? [];
        $txtRecords = self::dnsRecords($payload['txt_name'] ?? '', DNS_TXT);
        $cnameRecords = self::dnsRecords($this->domain, DNS_CNAME);

        $txtValue = $payload['txt_value'] ?? null;
        $cnameValue = $payload['value'] ?? null;

        $txtVerified = $txtValue && collect($txtRecords)->contains(fn (array $record) => str_contains((string) ($record['txt'] ?? ''), $txtValue));
        $cnameVerified = $cnameValue && collect($cnameRecords)->contains(fn (array $record) => str_contains(rtrim((string) ($record['target'] ?? ''), '.'), $cnameValue));

        if ($txtVerified || $cnameVerified) {
            $this->forceFill([
                'status' => self::STATUS_VERIFIED,
                'verified_at' => now(),
            ])->save();

            return true;
        }

        $this->forceFill([
            'status' => self::STATUS_FAILED,
        ])->save();

        return false;
    }

    public function activateSsl(): void
    {
        $this->forceFill([
            'status' => self::STATUS_ACTIVE,
            'ssl_status' => self::SSL_ACTIVE,
            'ssl_issued_at' => now(),
        ])->save();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function dnsRecords(string $host, int $type): array
    {
        if ($host === '') {
            return [];
        }

        $records = @dns_get_record($host, $type);

        return is_array($records) ? $records : [];
    }
}
