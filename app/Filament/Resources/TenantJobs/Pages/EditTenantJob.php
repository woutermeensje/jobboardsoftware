<?php

namespace App\Filament\Resources\TenantJobs\Pages;

use App\Filament\Resources\TenantJobs\TenantJobResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTenantJob extends EditRecord
{
    protected static string $resource = TenantJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
