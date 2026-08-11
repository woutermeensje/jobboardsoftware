<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\HasScopedValidationRules;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasDomains;
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
            'trial_ends_at',
            'subscribed_at',
            'settings',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(Domain::class)->where('is_primary', true);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
