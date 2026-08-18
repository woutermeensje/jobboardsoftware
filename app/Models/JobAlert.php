<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAlert extends Model
{
    protected $fillable = [
        'tenant_id',
        'email',
        'employment_types',
        'departments',
        'sectors',
        'organization_types',
    ];

    protected $casts = [
        'employment_types' => 'array',
        'departments' => 'array',
        'sectors' => 'array',
        'organization_types' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
