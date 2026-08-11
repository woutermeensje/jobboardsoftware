<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPlan extends Model
{
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

    public function formattedMonthlyPrice(): string
    {
        if ($this->monthly_price_cents === 0) {
            return 'Op maat';
        }

        return 'EUR '.number_format($this->monthly_price_cents / 100, 0, ',', '.').' / mnd';
    }
}
