<?php

namespace Database\Factories;

use App\Models\BillingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingPlan>
 */
class BillingPlanFactory extends Factory
{
    protected $model = BillingPlan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'monthly_price_cents' => fake()->numberBetween(2900, 9900),
            'currency' => 'eur',
            'stripe_price_id' => null,
            'features' => ['Unlimited jobs', 'Custom domain'],
            'limits' => ['jobs' => 10],
            'is_active' => true,
        ];
    }
}
