<?php

namespace App\Filament\Workspace\Resources\Applications\Pages;

use App\Filament\Workspace\Resources\Applications\ApplicationResource;
use App\Support\AdminActionNotifier;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        app(AdminActionNotifier::class)->notify('Sollicitatiestatus bijgewerkt', [
            'tenant_id' => $this->record->tenant_id,
            'tenant_naam' => $this->record->tenant?->name,
            'sollicitant' => $this->record->name,
            'email' => $this->record->email,
            'status' => $this->record->status,
        ], Filament::auth()->user());
    }
}
