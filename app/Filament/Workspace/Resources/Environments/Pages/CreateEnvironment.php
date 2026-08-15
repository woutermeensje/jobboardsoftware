<?php

namespace App\Filament\Workspace\Resources\Environments\Pages;

use App\Filament\Workspace\Resources\Environments\EnvironmentResource;
use App\Models\Domain;
use App\Models\Tenant;
use App\Support\AdminActionNotifier;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateEnvironment extends CreateRecord
{
    protected static string $resource = EnvironmentResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();
        $slug = Str::slug((string) ($data['slug'] ?? $data['name']));
        $settings = $data['settings'] ?? [];

        return [
            ...$data,
            'id' => $slug,
            'owner_user_id' => $user?->id,
            'slug' => $slug,
            'plan' => $user?->billingPlan?->key ?? Tenant::PLAN_STARTER,
            'status' => Tenant::STATUS_TRIAL,
            'billing_status' => $user?->billing_status ?? 'trial',
            'onboarding_step' => 'jobs',
            'trial_ends_at' => now()->addDays(14),
            'settings' => [
                'brand_name' => $settings['brand_name'] ?? $data['name'],
                'accent_color' => $settings['accent_color'] ?? '#2f5f80',
                'intro' => $settings['intro'] ?? 'View current jobs and apply directly.',
            ],
        ];
    }

    protected function afterCreate(): void
    {
        $this->record->domains()->create([
            'domain' => $this->record->slug.'.jobboardsoftware.co',
            'is_primary' => true,
            'status' => Domain::STATUS_ACTIVE,
            'ssl_status' => Domain::SSL_ACTIVE,
            'verified_at' => now(),
            'ssl_issued_at' => now(),
        ]);

        $user = Filament::auth()->user();

        $user?->forceFill([
            'onboarding_step' => 'jobs',
        ])->save();

        app(AdminActionNotifier::class)->notify('Environment aangemaakt', [
            'tenant_id' => $this->record->id,
            'tenant_naam' => $this->record->name,
            'slug' => $this->record->slug,
            'pakket' => $this->record->plan,
            'status' => $this->record->status,
            'domein' => $this->record->slug.'.jobboardsoftware.co',
            'onboarding_step' => $user?->onboarding_step,
        ], $user);
    }
}
