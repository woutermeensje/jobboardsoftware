<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_FAILED = 'failed';

    public const SSL_PENDING = 'pending';
    public const SSL_ACTIVE = 'active';
    public const SSL_FAILED = 'failed';

    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
        'ssl_issued_at' => 'datetime',
        'verification_payload' => 'array',
    ];

    public function isReadyForTraffic(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ssl_status === self::SSL_ACTIVE;
    }
}
