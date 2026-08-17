<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class TenantPublicCache
{
    public static function filterOptionsKey(string $tenantId): string
    {
        return self::key($tenantId, 'filter-options');
    }

    public static function totalJobsKey(string $tenantId): string
    {
        return self::key($tenantId, 'total-jobs');
    }

    public static function pricingPackagesKey(string $tenantId): string
    {
        return self::key($tenantId, 'pricing-packages');
    }

    public static function postJobPackagesKey(string $tenantId): string
    {
        return self::key($tenantId, 'post-job-packages');
    }

    public static function ttl(): int
    {
        return max(1, (int) config('cache.public_tenant_ttl', 600));
    }

    public static function remember(string $key, Closure $callback): mixed
    {
        return Cache::store(config('cache.default'))->remember($key, self::ttl(), $callback);
    }

    public static function forgetTenant(?string $tenantId): void
    {
        $tenantId = trim((string) $tenantId);

        if ($tenantId === '') {
            return;
        }

        foreach ([
            self::filterOptionsKey($tenantId),
            self::totalJobsKey($tenantId),
            self::pricingPackagesKey($tenantId),
            self::postJobPackagesKey($tenantId),
        ] as $key) {
            Cache::store(config('cache.default'))->forget($key);
        }
    }

    private static function key(string $tenantId, string $suffix): string
    {
        return "tenant-public:{$tenantId}:{$suffix}";
    }
}
