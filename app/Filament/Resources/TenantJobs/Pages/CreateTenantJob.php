<?php

namespace App\Filament\Resources\TenantJobs\Pages;

use App\Filament\Resources\TenantJobs\TenantJobResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantJob extends CreateRecord
{
    protected static string $resource = TenantJobResource::class;
}
