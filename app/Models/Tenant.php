<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\HasScopedValidationRules;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/** @use HasFactory<TenantFactory> */
class Tenant extends BaseTenant
{
    use HasDomains;
    use HasFactory;
    use HasScopedValidationRules;

    public const STATUS_TRIAL = 'trial';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const PLAN_STARTER = 'starter';

    public const PLAN_GROWTH = 'growth';

    public const PLAN_ENTERPRISE = 'enterprise';

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscribed_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'owner_user_id',
            'name',
            'slug',
            'plan',
            'status',
            'billing_status',
            'onboarding_step',
            'onboarding_completed_at',
            'trial_ends_at',
            'subscribed_at',
            'settings',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(Domain::class)->where('is_primary', true);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(TenantJob::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(TenantCompany::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(TenantPackage::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
