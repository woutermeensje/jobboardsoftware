<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_HIRED = 'hired';

    protected $fillable = [
        'tenant_id',
        'tenant_job_id',
        'name',
        'email',
        'phone',
        'motivation',
        'cv_path',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(TenantJob::class, 'tenant_job_id');
    }
}
