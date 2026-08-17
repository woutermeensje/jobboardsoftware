<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TenantOptionSettings
{
    private const CONFIG = [
        'sector' => [
            'title' => 'Sectors',
            'description' => 'Manage the sectors available for jobs in each environment.',
            'singular' => 'Sector',
            'field_label' => 'New sector',
            'placeholder' => 'For example: Healthcare',
            'settings_key' => 'custom_sectors',
            'route_name' => 'client.jobs-settings.sector',
            'store_route_name' => 'client.jobs-settings.sector.store',
            'button_label' => 'Add sector',
            'available_title' => 'Available sectors',
            'available_description' => 'These sectors can be used to group job content for each environment.',
            'empty_label' => 'No custom sectors',
            'aside_title' => 'Sector settings',
            'aside_description' => 'Supporting content for this settings screen will use this reusable right-hand block.',
            'required_error' => 'Enter a sector name.',
            'duplicate_error' => 'This sector already exists for this environment.',
            'success' => 'Sector added.',
        ],
        'categorie' => [
            'title' => 'Categories',
            'description' => 'Manage the categories available for jobs in each environment.',
            'singular' => 'Category',
            'field_label' => 'New category',
            'placeholder' => 'For example: Marketing',
            'settings_key' => 'custom_categories',
            'route_name' => 'client.jobs-settings.categorie',
            'store_route_name' => 'client.jobs-settings.categorie.store',
            'button_label' => 'Add category',
            'available_title' => 'Available categories',
            'available_description' => 'These categories can be used to organize vacancies for each environment.',
            'empty_label' => 'No custom categories',
            'aside_title' => 'Category settings',
            'aside_description' => 'Supporting content for this settings screen will use this reusable right-hand block.',
            'required_error' => 'Enter a category name.',
            'duplicate_error' => 'This category already exists for this environment.',
            'success' => 'Category added.',
        ],
        'job-type' => [
            'title' => 'Job types',
            'description' => 'Manage the employment types available for jobs in each environment.',
            'singular' => 'Job type',
            'field_label' => 'New job type',
            'placeholder' => 'For example: Volunteer',
            'settings_key' => 'custom_job_types',
            'route_name' => 'client.jobs-settings.job-type',
            'store_route_name' => 'client.jobs-settings.job-type.store',
            'button_label' => 'Add job type',
            'default_title' => 'Default job types',
            'available_title' => 'Available job types',
            'available_description' => 'These types can be used when jobs are created for each environment.',
            'empty_label' => 'No custom types',
            'aside_title' => 'Job type settings',
            'aside_description' => 'Supporting content for this settings screen will use this reusable right-hand block.',
            'required_error' => 'Enter a job type name.',
            'duplicate_error' => 'This job type already exists for this environment.',
            'success' => 'Job type added.',
        ],
        'organization-type' => [
            'title' => 'Organization types',
            'description' => 'Manage the organization types available for companies in each environment.',
            'singular' => 'Organization type',
            'field_label' => 'New organization type',
            'placeholder' => 'For example: Non-profit',
            'settings_key' => 'custom_organization_types',
            'route_name' => 'client.jobs-settings.organization-type',
            'store_route_name' => 'client.jobs-settings.organization-type.store',
            'button_label' => 'Add organization type',
            'available_title' => 'Available organization types',
            'available_description' => 'These organization types can be used to classify company and employer profiles.',
            'empty_label' => 'No custom organization types',
            'aside_title' => 'Organization settings',
            'aside_description' => 'Supporting content for this settings screen will use this reusable right-hand block.',
            'required_error' => 'Enter an organization type name.',
            'duplicate_error' => 'This organization type already exists for this environment.',
            'success' => 'Organization type added.',
        ],
    ];

    public static function has(string $type): bool
    {
        return array_key_exists($type, self::CONFIG);
    }

    /**
     * @return array<string, string>
     */
    public static function config(string $type): array
    {
        if (! self::has($type)) {
            throw new InvalidArgumentException("Unknown tenant option type [{$type}].");
        }

        return self::CONFIG[$type];
    }

    /**
     * @return array<int, string>
     */
    public static function defaults(string $type): array
    {
        return $type === 'job-type' ? JobTypeOptions::defaults() : [];
    }

    /**
     * @return array<int, string>
     */
    public static function customForTenant(Tenant $tenant, string $type): array
    {
        $settings = $tenant->settings ?? [];
        $key = self::config($type)['settings_key'];

        return collect($settings[$key] ?? [])
            ->map(fn (mixed $option): string => self::normalizeName((string) $option))
            ->filter()
            ->unique(fn (string $option): string => mb_strtolower($option))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function allForTenant(Tenant $tenant, string $type): array
    {
        return collect(self::defaults($type))
            ->merge(self::customForTenant($tenant, $type))
            ->unique(fn (string $option): string => mb_strtolower($option))
            ->values()
            ->all();
    }

    public static function normalizeName(string $value): string
    {
        return Str::of($value)->squish()->toString();
    }
}
