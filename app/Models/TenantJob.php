<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantJob extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id',
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

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
