<?php

namespace App\Support;

use App\Models\Tenant;

class BillingPlanCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function definitions(): array
    {
        $benefits = self::placeholderBenefits();

        return [
            [
                'key' => Tenant::PLAN_STARTER,
                'name' => 'Free plan',
                'description' => 'Try the platform and explore the core job board tools.',
                'monthly_price_cents' => 0,
                'currency' => 'eur',
                'features' => $benefits,
                'limits' => ['tenants' => 1, 'jobs' => 10, 'domains' => 1],
                'is_active' => true,
            ],
            [
                'key' => Tenant::PLAN_GROWTH,
                'name' => 'Standard plan',
                'description' => 'For job boards that are ready to publish and grow.',
                'monthly_price_cents' => 14900,
                'currency' => 'eur',
                'features' => $benefits,
                'limits' => ['tenants' => 1, 'jobs' => null, 'domains' => 1],
                'is_active' => true,
            ],
            [
                'key' => Tenant::PLAN_ENTERPRISE,
                'name' => 'Pro plan',
                'description' => 'For professional job boards with advanced growth needs.',
                'monthly_price_cents' => 24900,
                'currency' => 'eur',
                'features' => $benefits,
                'limits' => ['tenants' => 3, 'jobs' => null, 'domains' => 3],
                'is_active' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function definitionFor(string $key): ?array
    {
        foreach (self::definitions() as $definition) {
            if ($definition['key'] === $key) {
                return $definition;
            }
        }

        return null;
    }

    public static function priceLabel(string $key, int $monthlyPriceCents, string $currency = 'eur'): string
    {
        if ($monthlyPriceCents === 0 && $key === Tenant::PLAN_STARTER) {
            return self::trialLabel();
        }

        if ($monthlyPriceCents === 0) {
            return 'Op maat';
        }

        $prefix = strtolower($currency) === 'eur' ? '€' : strtoupper($currency).' ';

        return $prefix.number_format($monthlyPriceCents / 100, 0, ',', '.').' per month';
    }

    public static function trialLabel(): string
    {
        $trialDays = (int) config('billing.free_trial_days', 14);

        return $trialDays > 0 ? $trialDays.'-day free trial' : 'Free plan';
    }

    /**
     * @return array<int, array{name: string, price: string, description: string, features: array<int, string>}>
     */
    public static function publicPlans(): array
    {
        return array_map(
            fn (array $plan): array => [
                'name' => $plan['name'],
                'price' => self::priceLabel($plan['key'], $plan['monthly_price_cents'], $plan['currency']),
                'description' => $plan['description'],
                'features' => $plan['features'],
            ],
            self::definitions(),
        );
    }

    /**
     * @return array<int, string>
     */
    public static function sortOrder(): array
    {
        return [
            Tenant::PLAN_STARTER,
            Tenant::PLAN_GROWTH,
            Tenant::PLAN_ENTERPRISE,
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function placeholderBenefits(): array
    {
        return [
            'Placeholder benefit one',
            'Placeholder benefit two',
            'Placeholder benefit three',
            'Placeholder benefit four',
            'Placeholder benefit five',
            'Placeholder benefit six',
            'Placeholder benefit seven',
            'Placeholder benefit eight',
            'Placeholder benefit nine',
            'Placeholder benefit ten',
        ];
    }
}
