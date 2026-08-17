<?php

namespace App\Models;

use App\Support\TenantPublicCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id',
    'name',
    'price',
    'currency',
    'online_days',
    'description',
])]
class TenantPackage extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
        'online_days' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $package): void {
            TenantPublicCache::forgetTenant((string) $package->tenant_id);
        });

        static::deleted(function (self $package): void {
            TenantPublicCache::forgetTenant((string) $package->tenant_id);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(TenantJob::class);
    }

    public function displayLabel(): string
    {
        return sprintf(
            '%s - %s %s - %d days',
            $this->name,
            $this->currency,
            number_format((float) $this->price, 2),
            $this->online_days,
        );
    }
}
