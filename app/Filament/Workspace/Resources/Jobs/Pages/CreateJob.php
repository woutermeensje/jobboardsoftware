<?php

namespace App\Filament\Workspace\Resources\Jobs\Pages;

use App\Filament\Workspace\Resources\Jobs\JobResource;
use App\Models\TenantJob;
use App\Support\AdminActionNotifier;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateJob extends CreateRecord
{
    protected static string $resource = JobResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();

        abort_unless(
            $user?->isAdmin() || $user?->ownedTenants()->whereKey($data['tenant_id'] ?? null)->exists(),
            403,
        );

        if (($data['status'] ?? null) === TenantJob::STATUS_PUBLISHED && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = Filament::auth()->user();

        $user?->forceFill([
            'onboarding_step' => 'complete',
            'onboarding_completed_at' => now(),
        ])->save();

        app(AdminActionNotifier::class)->notify('Vacature aangemaakt', [
            'tenant_id' => $this->record->tenant_id,
            'tenant_naam' => $this->record->tenant?->name,
            'vacature' => $this->record->title,
            'slug' => $this->record->slug,
            'status' => $this->record->status,
            'onboarding_step' => $user?->onboarding_step,
        ], $user);
    }
}
