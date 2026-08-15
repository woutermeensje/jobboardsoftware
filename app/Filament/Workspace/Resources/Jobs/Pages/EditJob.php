<?php

namespace App\Filament\Workspace\Resources\Jobs\Pages;

use App\Filament\Workspace\Resources\Jobs\JobResource;
use App\Models\TenantJob;
use App\Support\AdminActionNotifier;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditJob extends EditRecord
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === TenantJob::STATUS_PUBLISHED && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        app(AdminActionNotifier::class)->notify('Vacature bijgewerkt', [
            'tenant_id' => $this->record->tenant_id,
            'tenant_naam' => $this->record->tenant?->name,
            'vacature' => $this->record->title,
            'slug' => $this->record->slug,
            'status' => $this->record->status,
        ], Filament::auth()->user());
    }
}
