<?php

namespace App\Filament\Workspace\Resources\Domains\Pages;

use App\Filament\Workspace\Resources\Domains\DomainResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDomain extends ViewRecord
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DomainResource::checkDnsAction(),
            DomainResource::activateSslAction(),
            EditAction::make(),
        ];
    }
}
