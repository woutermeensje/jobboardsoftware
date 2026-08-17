<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantJob;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TenantJob>
 */
class TenantJobFactory extends Factory
{
    protected $model = TenantJob::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->jobTitle();

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'department' => fake()->word(),
            'location' => fake()->city(),
            'country' => 'NL',
            'is_remote' => false,
            'employment_type' => 'full-time',
            'salary_range' => null,
            'intro' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'status' => TenantJob::STATUS_PUBLISHED,
            'published_at' => now(),
            'closes_at' => null,
        ];
    }
}
