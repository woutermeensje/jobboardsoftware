<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PublicUploadStorage
{
    public static function diskName(): string
    {
        return config('filesystems.public_uploads_disk', 'uploads');
    }

    public static function store(UploadedFile $file, string $directory, string|int|null $tenantId = null): string
    {
        $directory = trim($directory, '/');
        $tenantPrefix = self::tenantPrefix($tenantId);
        $path = trim($tenantPrefix.'/'.$directory, '/');

        $storedPath = $file->storePublicly($path, self::diskName());

        if (! is_string($storedPath) || $storedPath === '') {
            throw new RuntimeException('The uploaded file could not be stored.');
        }

        return $storedPath;
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (self::shouldUseLegacyTenantAssetUrl($path)) {
            return tenant_asset($path);
        }

        return Storage::disk(self::diskName())->url($path);
    }

    private static function tenantPrefix(string|int|null $tenantId): string
    {
        $tenantId ??= tenant('id');

        return $tenantId ? 'tenants/'.trim((string) $tenantId, '/') : '';
    }

    private static function shouldUseLegacyTenantAssetUrl(string $path): bool
    {
        if (! function_exists('tenant_asset') || ! tenant('id')) {
            return false;
        }

        if (Str::startsWith($path, 'tenants/')) {
            return false;
        }

        $disk = config('filesystems.disks.'.self::diskName(), []);

        return ($disk['driver'] ?? null) === 'local';
    }
}
