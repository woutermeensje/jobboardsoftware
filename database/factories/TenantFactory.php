<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();
        $slug = Str::slug($name).'-'.Str::lower(Str::random(5));

        return [
            'id' => $slug,
            'owner_user_id' => User::factory(),
            'name' => $name,
            'slug' => $slug,
            'plan' => Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_TRIAL,
            'billing_status' => 'trial',
            'onboarding_step' => 'jobs',
            'trial_ends_at' => now()->addDays(14),
            'settings' => [
                'brand_name' => $name,
                'primary_color' => '#2f5f80',
                'accent_color' => '#2f5f80',
                'homepage_title' => 'Search all jobs',
                'homepage_subtitle' => 'Jobs, internships and roles at '.$name.'.',
                'intro' => 'View current jobs and apply directly.',
            ],
        ];
    }
}
