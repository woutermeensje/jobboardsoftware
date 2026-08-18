<?php

namespace Database\Seeders;

use App\Models\BillingPlan;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use App\Support\BillingPlanCatalog;
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
        $plans = collect(BillingPlanCatalog::definitions())->map(fn (array $plan) => BillingPlan::updateOrCreate(
            ['key' => $plan['key']],
            [
                'name' => $plan['name'],
                'description' => $plan['description'],
                'monthly_price_cents' => $plan['monthly_price_cents'],
                'currency' => $plan['currency'],
                'features' => $plan['features'],
                'limits' => $plan['limits'],
                'is_active' => $plan['is_active'],
            ],
        ));

        User::updateOrCreate(
            ['tenant_id' => null, 'email' => 'wouter@inhuren.com'],
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
            ['tenant_id' => null, 'email' => 'demo@jobboardsoftware.co'],
            [
                'name' => 'Demo Owner',
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
                'trial_ends_at' => now()->addDays((int) config('billing.free_trial_days', 14)),
                'settings' => [
                    'brand_name' => 'Acme Careers',
                    'primary_color' => '#2f5f80',
                    'secondary_color' => '#d99a5b',
                    'accent_color' => '#d99a5b',
                    'homepage_title' => 'Search all jobs',
                    'homepage_subtitle' => 'Jobs, internships and roles at Acme Careers.',
                    'intro' => 'Find your next role at Acme Careers.',
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
                    'salary_range' => 'Upon request',
                    'intro' => 'Help build a growing platform and make an immediate impact.',
                    'description' => 'You work with a compact team on concrete projects, clear goals and a smooth candidate experience.',
                    'status' => TenantJob::STATUS_PUBLISHED,
                    'published_at' => now()->subDay(),
                ],
            );
        }
    }
}
