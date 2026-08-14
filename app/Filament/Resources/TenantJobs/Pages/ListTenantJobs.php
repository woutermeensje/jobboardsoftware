<?php

namespace App\Filament\Resources\TenantJobs\Pages;

use App\Filament\Resources\TenantJobs\TenantJobResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTenantJobs extends ListRecords
{
    protected static string $resource = TenantJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
