<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->syncPlans([
            [
                'key' => 'starter',
                'name' => 'Free plan',
                'description' => 'Try the platform and explore the core job board tools.',
                'monthly_price_cents' => 0,
                'currency' => 'eur',
                'features' => $this->placeholderBenefits(),
                'limits' => ['tenants' => 1, 'jobs' => 10, 'domains' => 1],
                'is_active' => true,
            ],
            [
                'key' => 'growth',
                'name' => 'Standard plan',
                'description' => 'For job boards that are ready to publish and grow.',
                'monthly_price_cents' => 14900,
                'currency' => 'eur',
                'features' => $this->placeholderBenefits(),
                'limits' => ['tenants' => 1, 'jobs' => null, 'domains' => 1],
                'is_active' => true,
            ],
            [
                'key' => 'enterprise',
                'name' => 'Pro plan',
                'description' => 'For professional job boards with advanced growth needs.',
                'monthly_price_cents' => 24900,
                'currency' => 'eur',
                'features' => $this->placeholderBenefits(),
                'limits' => ['tenants' => 3, 'jobs' => null, 'domains' => 3],
                'is_active' => true,
            ],
        ]);
    }

    public function down(): void
    {
        $this->syncPlans([
            [
                'key' => 'starter',
                'name' => 'Starter',
                'description' => 'For a niche job board or MVP with a custom domain.',
                'monthly_price_cents' => 4900,
                'currency' => 'eur',
                'features' => ['1 job board', 'Custom domain', 'Basic management portal', 'Job management'],
                'limits' => ['tenants' => 1, 'jobs' => 50, 'domains' => 1],
                'is_active' => true,
            ],
            [
                'key' => 'growth',
                'name' => 'Growth',
                'description' => 'For agencies and communities managing multiple job boards.',
                'monthly_price_cents' => 14900,
                'currency' => 'eur',
                'features' => ['3 job boards', 'Multiple domains', 'Advanced management', 'Priority support'],
                'limits' => ['tenants' => 3, 'jobs' => 250, 'domains' => 6],
                'is_active' => true,
            ],
            [
                'key' => 'enterprise',
                'name' => 'Platform',
                'description' => 'For white-label software, custom integrations and higher volumes.',
                'monthly_price_cents' => 0,
                'currency' => 'eur',
                'features' => ['Unlimited tenants', 'Custom integrations', 'Dedicated onboarding'],
                'limits' => ['tenants' => null, 'jobs' => null, 'domains' => null],
                'is_active' => true,
            ],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $plans
     */
    private function syncPlans(array $plans): void
    {
        $now = now();

        foreach ($plans as $plan) {
            $attributes = [
                'name' => $plan['name'],
                'description' => $plan['description'],
                'monthly_price_cents' => $plan['monthly_price_cents'],
                'currency' => $plan['currency'],
                'features' => json_encode($plan['features']),
                'limits' => json_encode($plan['limits']),
                'is_active' => $plan['is_active'],
                'updated_at' => $now,
            ];

            if (DB::table('billing_plans')->where('key', $plan['key'])->exists()) {
                DB::table('billing_plans')
                    ->where('key', $plan['key'])
                    ->update($attributes);

                continue;
            }

            DB::table('billing_plans')->insert([
                'key' => $plan['key'],
                ...$attributes,
                'created_at' => $now,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function placeholderBenefits(): array
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
};
