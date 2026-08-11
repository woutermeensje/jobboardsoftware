<?php

namespace Database\Seeders;

use App\Models\BillingPlan;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $plans = collect([
            [
                'key' => Tenant::PLAN_STARTER,
                'name' => 'Starter',
                'description' => 'Voor een niche jobboard of MVP met een eigen domein.',
                'monthly_price_cents' => 4900,
                'features' => ['1 jobboard', 'Eigen domein', 'Basis beheeromgeving', 'Vacatures beheren'],
                'limits' => ['tenants' => 1, 'jobs' => 50, 'domains' => 1],
            ],
            [
                'key' => Tenant::PLAN_GROWTH,
                'name' => 'Growth',
                'description' => 'Voor bureaus en communities die meerdere jobboards beheren.',
                'monthly_price_cents' => 14900,
                'features' => ['3 jobboards', 'Meerdere domeinen', 'Uitgebreid beheer', 'Prioriteit support'],
                'limits' => ['tenants' => 3, 'jobs' => 250, 'domains' => 6],
            ],
            [
                'key' => Tenant::PLAN_ENTERPRISE,
                'name' => 'Platform',
                'description' => 'Voor white label software, maatwerk integraties en grotere volumes.',
                'monthly_price_cents' => 0,
                'features' => ['Onbeperkte tenants', 'Maatwerk integraties', 'Dedicated onboarding'],
                'limits' => ['tenants' => null, 'jobs' => null, 'domains' => null],
            ],
        ])->map(fn (array $plan) => BillingPlan::updateOrCreate(
            ['key' => $plan['key']],
            [
                'name' => $plan['name'],
                'description' => $plan['description'],
                'monthly_price_cents' => $plan['monthly_price_cents'],
                'currency' => 'eur',
                'features' => $plan['features'],
                'limits' => $plan['limits'],
                'is_active' => true,
            ],
        ));

        User::updateOrCreate(
            ['email' => 'wouter@inhuren.com'],
            [
                'name' => 'Wouter',
                'company_name' => 'JobBoardSoftware',
                'password' => 'JobboardAdmin!2026',
                'role' => User::ROLE_ADMIN,
                'billing_status' => 'active',
                'onboarding_step' => 'complete',
                'onboarding_completed_at' => now(),
            ],
        );

        $owner = User::updateOrCreate(
            ['email' => 'demo@jobboardsoftware.co'],
            [
                'name' => 'Demo Eigenaar',
                'company_name' => 'Acme Careers',
                'password' => 'DemoPassword!2026',
                'role' => User::ROLE_TENANT_OWNER,
                'billing_plan_id' => $plans->firstWhere('key', Tenant::PLAN_STARTER)?->id,
                'billing_status' => 'trial',
                'onboarding_step' => 'jobs',
            ],
        );

        $tenant = Tenant::updateOrCreate(
            ['id' => 'acme-careers'],
            [
                'owner_user_id' => $owner->id,
                'name' => 'Acme Careers',
                'slug' => 'acme-careers',
                'plan' => Tenant::PLAN_STARTER,
                'status' => Tenant::STATUS_ACTIVE,
                'billing_status' => 'trial',
                'onboarding_step' => 'jobs',
                'trial_ends_at' => now()->addDays(14),
                'settings' => [
                    'brand_name' => 'Acme Careers',
                    'accent_color' => '#2f5f80',
                    'intro' => 'Vind je volgende rol bij Acme Careers.',
                ],
            ],
        );

        $tenant->domains()->updateOrCreate(
            ['domain' => 'acme.test'],
            [
                'is_primary' => true,
                'status' => Domain::STATUS_ACTIVE,
                'ssl_status' => Domain::SSL_ACTIVE,
                'verified_at' => now(),
                'ssl_issued_at' => now(),
            ],
        );

        foreach ([
            ['Laravel Developer', 'Development', 'Amsterdam', 'Fulltime'],
            ['Recruitment Marketeer', 'Marketing', 'Rotterdam', 'Parttime'],
            ['Customer Success Manager', 'Customer Success', 'Utrecht', 'Hybrid'],
        ] as [$title, $department, $location, $type]) {
            TenantJob::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'department' => $department,
                    'location' => $location,
                    'employment_type' => $type,
                    'salary_range' => 'In overleg',
                    'intro' => 'Werk mee aan een groeiend platform en maak direct impact.',
                    'description' => 'Je werkt samen met een compact team aan concrete projecten, duidelijke doelen en een prettige kandidaatervaring.',
                    'status' => TenantJob::STATUS_PUBLISHED,
                    'published_at' => now()->subDay(),
                ],
            );
        }
    }
}
