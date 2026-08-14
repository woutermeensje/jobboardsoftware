<?php

namespace App\Filament\Resources\TenantJobs\Pages;

use App\Filament\Resources\TenantJobs\TenantJobResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTenantJob extends ViewRecord
{
    protected static string $resource = TenantJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
