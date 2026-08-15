<?php

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'tenant_job_id' => fn (array $attributes) => TenantJob::factory()->create([
                'tenant_id' => $attributes['tenant_id'],
            ])->id,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'motivation' => fake()->paragraph(),
            'cv_path' => null,
            'status' => JobApplication::STATUS_NEW,
        ];
    }
}
