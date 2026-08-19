<?php

namespace App\Models;

use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
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

    public const CLOUD_STATUS_PENDING = 'pending';

    public const CLOUD_STATUS_VERIFIED = 'verified';

    public const CLOUD_STATUS_FAILED = 'failed';

    public const CLOUD_STATUS_DISABLED = 'disabled';

    public const CLOUDFLARE_NONE = 'none';

    public const CLOUDFLARE_DNS = 'dns';

    public const CLOUDFLARE_DNS_PROXY = 'dns_proxy';

    public const VERIFICATION_REAL_TIME = 'real_time';

    public const VERIFICATION_PRE_VERIFICATION = 'pre_verification';

    public const WWW_TO_ROOT = 'www_to_root';

    public const ROOT_TO_WWW = 'root_to_www';

    protected $casts = [
        'is_primary' => 'boolean',
        'wildcard_enabled' => 'boolean',
        'allow_downtime' => 'boolean',
        'verified_at' => 'datetime',
        'ssl_issued_at' => 'datetime',
        'cloud_last_verified_at' => 'datetime',
        'verification_payload' => 'array',
    ];

    public function isReadyForTraffic(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ssl_status === self::SSL_ACTIVE;
    }

    public function usesLaravelCloud(): bool
    {
        return filled($this->cloud_domain_id)
            || (($this->verification_payload['provider'] ?? null) === 'laravel_cloud');
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function syncFromLaravelCloud(array $response, ?string $environmentId = null): void
    {
        $resource = $response['data'] ?? $response;

        if (! is_array($resource)) {
            return;
        }

        $attributes = $resource['attributes'] ?? [];
        $attributes = is_array($attributes) ? $attributes : [];

        $cloudDomainId = (string) ($resource['id'] ?? $this->cloud_domain_id);
        $cloudHostnameStatus = self::cloudStatus($attributes['hostname_status'] ?? $this->cloud_hostname_status);
        $cloudSslStatus = self::cloudStatus($attributes['ssl_status'] ?? $this->cloud_ssl_status);
        $cloudOriginStatus = self::cloudStatus($attributes['origin_status'] ?? $this->cloud_origin_status);
        $lastVerifiedAt = self::timestamp($attributes['last_verified_at'] ?? null);
        $cloudEnvironmentId = $environmentId
            ?? data_get($resource, 'relationships.environment.data.id')
            ?? $this->cloud_environment_id;

        $verificationPayload = $this->verification_payload ?? [];
        $verificationPayload['provider'] = 'laravel_cloud';
        $verificationPayload['cloud_domain_id'] = $cloudDomainId;
        $verificationPayload['dns_records'] = $this->extractCloudDnsRecords($attributes);
        $verificationPayload['raw_dns_records'] = [
            'root' => $attributes['dns_records'] ?? null,
            'wildcard' => data_get($attributes, 'wildcard.dns_records'),
            'www' => data_get($attributes, 'www.dns_records'),
        ];
        $verificationPayload['action_required'] = $attributes['action_required'] ?? null;
        $verificationPayload['last_verified_at'] = $attributes['last_verified_at'] ?? null;

        $isVerified = in_array($cloudHostnameStatus, [self::CLOUD_STATUS_VERIFIED], true)
            || in_array($cloudOriginStatus, [self::CLOUD_STATUS_VERIFIED], true);
        $sslIsActive = $cloudSslStatus === self::CLOUD_STATUS_VERIFIED;

        $this->forceFill([
            'cloud_domain_id' => $cloudDomainId ?: null,
            'cloud_environment_id' => $cloudEnvironmentId ?: null,
            'cloud_hostname_status' => $cloudHostnameStatus,
            'cloud_ssl_status' => $cloudSslStatus,
            'cloud_origin_status' => $cloudOriginStatus,
            'cloud_action_required' => $attributes['action_required'] ?? null,
            'cloud_last_verified_at' => $lastVerifiedAt,
            'status' => self::statusFromCloud($cloudHostnameStatus, $cloudSslStatus, $cloudOriginStatus),
            'ssl_status' => self::sslStatusFromCloud($cloudSslStatus),
            'verified_at' => $isVerified ? ($this->verified_at ?? $lastVerifiedAt ?? now()) : $this->verified_at,
            'ssl_issued_at' => $sslIsActive ? ($this->ssl_issued_at ?? $lastVerifiedAt ?? now()) : $this->ssl_issued_at,
            'verification_payload' => $verificationPayload,
        ])->save();
    }

    /**
     * @return array<int, array{section: string, purpose: string, type: string, name: string, value: string}>
     */
    public function cloudDnsRecords(): array
    {
        $records = $this->verification_payload['dns_records'] ?? [];

        if (! is_array($records)) {
            return [];
        }

        return collect($records)
            ->filter(fn (mixed $record): bool => is_array($record))
            ->map(fn (array $record): array => [
                'section' => (string) ($record['section'] ?? 'root'),
                'purpose' => (string) ($record['purpose'] ?? 'DNS'),
                'type' => (string) ($record['type'] ?? 'DNS'),
                'name' => (string) ($record['name'] ?? ''),
                'value' => (string) ($record['value'] ?? ''),
            ])
            ->values()
            ->all();
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

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, array{section: string, purpose: string, type: string, name: string, value: string}>
     */
    private function extractCloudDnsRecords(array $attributes): array
    {
        $records = [];
        $groups = [
            'root' => $attributes['dns_records'] ?? [],
            'wildcard' => data_get($attributes, 'wildcard.dns_records', []),
            'www' => data_get($attributes, 'www.dns_records', []),
        ];

        foreach ($groups as $section => $dnsRecords) {
            if (! is_array($dnsRecords)) {
                continue;
            }

            foreach ($dnsRecords as $purpose => $recordValue) {
                foreach ($this->recordRows($section, (string) $purpose, $recordValue) as $record) {
                    $records[] = $record;
                }
            }
        }

        return $records;
    }

    /**
     * @return array<int, array{section: string, purpose: string, type: string, name: string, value: string}>
     */
    private function recordRows(string $section, string $purpose, mixed $recordValue): array
    {
        if ($recordValue === null || $recordValue === '') {
            return [];
        }

        if (is_array($recordValue) && array_is_list($recordValue)) {
            return collect($recordValue)
                ->flatMap(fn (mixed $value): array => $this->recordRows($section, $purpose, $value))
                ->values()
                ->all();
        }

        if (is_array($recordValue)) {
            $name = (string) ($recordValue['name'] ?? $recordValue['host'] ?? $this->nameForDnsPurpose($section, $purpose));
            $value = (string) ($recordValue['value'] ?? $recordValue['target'] ?? '');

            if ($value === '') {
                return [];
            }

            return [[
                'section' => $section,
                'purpose' => self::dnsPurposeLabel($purpose),
                'type' => self::dnsTypeForPurpose($purpose, $recordValue['type'] ?? null),
                'name' => $name,
                'value' => $value,
            ]];
        }

        return [[
            'section' => $section,
            'purpose' => self::dnsPurposeLabel($purpose),
            'type' => self::dnsTypeForPurpose($purpose),
            'name' => $this->nameForDnsPurpose($section, $purpose),
            'value' => (string) $recordValue,
        ]];
    }

    private function nameForDnsPurpose(string $section, string $purpose): string
    {
        $domain = match ($section) {
            'wildcard' => '*.'.$this->domain,
            'www' => 'www.'.$this->domain,
            default => $this->domain,
        };

        return match ($purpose) {
            'pre_verification' => '_cf-custom-hostname.'.$domain,
            'dcv' => '_acme-challenge.'.ltrim($domain, '*.'),
            default => $domain,
        };
    }

    private static function dnsPurposeLabel(string $purpose): string
    {
        return match ($purpose) {
            'ssl' => 'SSL',
            'pre_verification' => 'Ownership',
            'origin' => 'Origin',
            'origin_cname' => 'Origin',
            'dcv' => 'DCV',
            default => str($purpose)->replace('_', ' ')->headline()->toString(),
        };
    }

    private static function dnsTypeForPurpose(string $purpose, mixed $type = null): string
    {
        $type = trim((string) $type);

        if ($type !== '') {
            return strtoupper($type);
        }

        return match ($purpose) {
            'pre_verification' => 'TXT',
            'origin_cname', 'dcv' => 'CNAME',
            'origin' => 'A',
            default => 'DNS',
        };
    }

    private static function statusFromCloud(?string $hostnameStatus, ?string $sslStatus, ?string $originStatus): string
    {
        $statuses = array_filter([$hostnameStatus, $sslStatus, $originStatus]);

        if (array_intersect($statuses, [self::CLOUD_STATUS_FAILED, self::CLOUD_STATUS_DISABLED])) {
            return self::STATUS_FAILED;
        }

        if ($hostnameStatus === self::CLOUD_STATUS_VERIFIED
            && $sslStatus === self::CLOUD_STATUS_VERIFIED
            && $originStatus === self::CLOUD_STATUS_VERIFIED) {
            return self::STATUS_ACTIVE;
        }

        if ($hostnameStatus === self::CLOUD_STATUS_VERIFIED || $sslStatus === self::CLOUD_STATUS_VERIFIED) {
            return self::STATUS_VERIFIED;
        }

        return self::STATUS_PENDING;
    }

    private static function sslStatusFromCloud(?string $sslStatus): string
    {
        return match ($sslStatus) {
            self::CLOUD_STATUS_VERIFIED => self::SSL_ACTIVE,
            self::CLOUD_STATUS_FAILED, self::CLOUD_STATUS_DISABLED => self::SSL_FAILED,
            default => self::SSL_PENDING,
        };
    }

    private static function cloudStatus(mixed $status): ?string
    {
        $status = str($status ?? '')->lower()->toString();

        return $status === '' ? null : $status;
    }

    private static function timestamp(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
