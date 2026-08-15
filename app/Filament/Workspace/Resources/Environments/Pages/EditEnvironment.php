<?php

namespace App\Filament\Workspace\Resources\Environments\Pages;

use App\Filament\Workspace\Resources\Environments\EnvironmentResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEnvironment extends EditRecord
{
    protected static string $resource = EnvironmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
