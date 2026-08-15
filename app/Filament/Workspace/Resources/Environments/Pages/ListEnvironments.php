<?php

namespace App\Filament\Workspace\Resources\Environments\Pages;

use App\Filament\Workspace\Resources\Environments\EnvironmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnvironments extends ListRecords
{
    protected static string $resource = EnvironmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
