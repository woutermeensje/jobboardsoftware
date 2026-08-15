<?php

namespace App\Filament\Workspace\Resources\Domains\Pages;

use App\Filament\Workspace\Resources\Domains\DomainResource;
use App\Models\Domain;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditDomain extends EditRecord
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DomainResource::checkDnsAction(),
            DomainResource::activateSslAction(),
            ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['domain'])) {
            $data['domain'] = Str::of((string) $data['domain'])
                ->lower()
                ->replace(['https://', 'http://'], '')
                ->before('/')
                ->trim()
                ->toString();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (! $this->record->is_primary) {
            return;
        }

        Domain::query()
            ->where('tenant_id', $this->record->tenant_id)
            ->whereKeyNot($this->record->getKey())
            ->update(['is_primary' => false]);
    }
}
