<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'company_name',
    'billing_plan_id',
    'billing_status',
    'onboarding_step',
    'onboarding_completed_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable;

    public const ROLE_WERKZOEKENDE = 'werkzoekende';

    public const ROLE_WERKGEVER = 'werkgever';

    public const ROLE_TENANT_OWNER = 'tenant_owner';

    public const ROLE_ADMIN = 'admin';

    public function isWerkzoekende(): bool
    {
        return $this->role === self::ROLE_WERKZOEKENDE;
    }

    public function isWerkgever(): bool
    {
        return $this->role === self::ROLE_WERKGEVER;
    }

    public function isTenantOwner(): bool
    {
        return $this->role === self::ROLE_TENANT_OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_user_id');
    }

    public function billingPlan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
