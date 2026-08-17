<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Str;

class JobTypeOptions
{
    public const DEFAULT_TYPES = [
        'Part time',
        'Full time',
        'Freelance',
        'Temporary',
        'Internship',
        'Remote',
    ];

    /**
     * @return array<int, string>
     */
    public static function defaults(): array
    {
        return self::DEFAULT_TYPES;
    }

    /**
     * @return array<int, string>
     */
    public static function customForTenant(Tenant $tenant): array
    {
        $settings = $tenant->settings ?? [];

        return collect($settings['custom_job_types'] ?? [])
            ->map(fn (mixed $type): string => self::normalizeName((string) $type))
            ->filter()
            ->unique(fn (string $type): string => mb_strtolower($type))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function allForTenant(Tenant $tenant): array
    {
        return collect(self::DEFAULT_TYPES)
            ->merge(self::customForTenant($tenant))
            ->unique(fn (string $type): string => mb_strtolower($type))
            ->values()
            ->all();
    }

    public static function normalizeName(string $value): string
    {
        return Str::of($value)->squish()->toString();
    }
}
