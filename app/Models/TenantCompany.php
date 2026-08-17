<?php

namespace App\Models;

use App\Support\TenantPublicCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'organization_name',
    'name',
    'company_url',
    'sector',
    'organization_type',
    'slug',
    'contact_first_name',
    'contact_last_name',
    'contact_name',
    'contact_email',
    'contact_phone',
    'logo_path',
    'description',
])]
class TenantCompany extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (self $company): void {
            TenantPublicCache::forgetTenant((string) $company->tenant_id);
        });

        static::deleted(function (self $company): void {
            TenantPublicCache::forgetTenant((string) $company->tenant_id);
        });
    }

    /**
     * @return array<int, string>
     */
    public function sectorValues(): array
    {
        return self::classificationValuesFromRaw($this->sector);
    }

    /**
     * @return array<int, string>
     */
    public function organizationTypeValues(): array
    {
        return self::classificationValuesFromRaw($this->organization_type);
    }

    /**
     * @return array<int, string>
     */
    public static function classificationValuesFromRaw(mixed $value): array
    {
        if (is_array($value)) {
            $values = $value;
        } elseif (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return [];
            }

            $decoded = json_decode($value, true);
            $values = json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                ? $decoded
                : [$value];
        } elseif ($value === null) {
            return [];
        } else {
            $values = [$value];
        }

        return collect($values)
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->unique(fn (string $value): string => mb_strtolower($value))
            ->values()
            ->all();
    }

    public static function encodeClassificationValues(array $values): ?string
    {
        $values = self::classificationValuesFromRaw($values);

        return $values === []
            ? null
            : json_encode($values, JSON_UNESCAPED_UNICODE);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
