<?php

namespace App\Models;

use Database\Factories\TenantJobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @use HasFactory<TenantJobFactory> */
class TenantJob extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id',
        'tenant_company_id',
        'tenant_package_id',
        'company_name',
        'company_logo_path',
        'contact_name',
        'contact_email',
        'contact_phone',
        'submitted_by_user_id',
        'title',
        'slug',
        'department',
        'location',
        'employment_type',
        'salary_range',
        'intro',
        'description',
        'status',
        'published_at',
        'closes_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'closes_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(TenantCompany::class, 'tenant_company_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TenantPackage::class, 'tenant_package_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
