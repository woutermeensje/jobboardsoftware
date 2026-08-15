<?php

namespace App\Filament\Workspace\Resources\Domains\Pages;

use App\Filament\Workspace\Resources\Domains\DomainResource;
use App\Models\Domain;
use App\Support\AdminActionNotifier;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateDomain extends CreateRecord
{
    protected static string $resource = DomainResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();

        abort_unless(
            $user?->isAdmin() || $user?->ownedTenants()->whereKey($data['tenant_id'] ?? null)->exists(),
            403,
        );

        $domain = $this->normalizeDomain((string) $data['domain']);
        $verificationToken = Str::random(40);

        return [
            ...$data,
            'domain' => $domain,
            'status' => Domain::STATUS_PENDING,
            'ssl_status' => Domain::SSL_PENDING,
            'verification_token' => $verificationToken,
            'verification_payload' => [
                'type' => 'CNAME',
                'name' => $domain,
                'value' => 'cname.jobboardsoftware.co',
                'txt_name' => '_jobboardsoftware.'.$domain,
                'txt_value' => $verificationToken,
            ],
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        if ($record->is_primary) {
            Domain::query()
                ->where('tenant_id', $record->tenant_id)
                ->whereKeyNot($record->getKey())
                ->update(['is_primary' => false]);
        }

        return $record;
    }

    protected function afterCreate(): void
    {
        $tenant = $this->record->tenant;
        $user = Filament::auth()->user();

        $tenant?->forceFill([
            'onboarding_step' => 'jobs',
        ])->save();

        $user?->forceFill([
            'onboarding_step' => 'jobs',
        ])->save();

        app(AdminActionNotifier::class)->notify('Domein toegevoegd', [
            'tenant_id' => $this->record->tenant_id,
            'tenant_naam' => $tenant?->name,
            'domein' => $this->record->domain,
            'is_primary' => $this->record->is_primary,
            'onboarding_step' => $user?->onboarding_step,
        ], $user);
    }

    private function normalizeDomain(string $domain): string
    {
        return Str::of($domain)
            ->lower()
            ->replace(['https://', 'http://'], '')
            ->before('/')
            ->trim()
            ->toString();
    }
}
