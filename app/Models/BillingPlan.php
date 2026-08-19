<?php

namespace App\Models;

use App\Support\BillingPlanCatalog;
use Database\Factories\BillingPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @use HasFactory<BillingPlanFactory> */
class BillingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'monthly_price_cents',
        'currency',
        'stripe_price_id',
        'features',
        'limits',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function displayName(): string
    {
        return (string) ($this->catalogDefinition()['name'] ?? $this->name);
    }

    public function displayDescription(): ?string
    {
        return $this->catalogDefinition()['description'] ?? $this->description;
    }

    /**
     * @return array<int, string>
     */
    public function displayFeatures(): array
    {
        return $this->catalogDefinition()['features'] ?? ($this->features ?? []);
    }

    public function effectiveMonthlyPriceCents(): int
    {
        return (int) ($this->catalogDefinition()['monthly_price_cents'] ?? $this->monthly_price_cents);
    }

    public function displayCurrency(): string
    {
        return (string) ($this->catalogDefinition()['currency'] ?? $this->currency);
    }

    public function formattedMonthlyPrice(): string
    {
        return BillingPlanCatalog::priceLabel(
            $this->key,
            $this->effectiveMonthlyPriceCents(),
            $this->displayCurrency(),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function catalogDefinition(): ?array
    {
        return BillingPlanCatalog::definitionFor($this->key);
    }
}
