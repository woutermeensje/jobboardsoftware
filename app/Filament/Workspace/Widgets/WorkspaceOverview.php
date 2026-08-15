<?php

namespace App\Filament\Workspace\Widgets;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WorkspaceOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Workspace overview';

    protected ?string $description = 'Your job boards, vacancies, domains, and candidate pipeline.';

    protected function getStats(): array
    {
        $user = Filament::auth()->user();
        $ownerId = $user?->id ?? 0;

        $tenantQuery = Tenant::query()->where('owner_user_id', $ownerId);
        $jobQuery = TenantJob::query()->whereHas('tenant', fn ($query) => $query->where('owner_user_id', $ownerId));
        $domainQuery = Domain::query()->whereHas('tenant', fn ($query) => $query->where('owner_user_id', $ownerId));
        $applicationQuery = JobApplication::query()->whereHas('tenant', fn ($query) => $query->where('owner_user_id', $ownerId));

        return [
            Stat::make('Environments', (clone $tenantQuery)->count())
                ->description((clone $tenantQuery)->where('status', Tenant::STATUS_ACTIVE)->count().' active')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('primary'),
            Stat::make('Published jobs', (clone $jobQuery)->where('status', TenantJob::STATUS_PUBLISHED)->count())
                ->description((clone $jobQuery)->where('status', TenantJob::STATUS_DRAFT)->count().' drafts')
                ->icon(Heroicon::OutlinedBriefcase)
                ->color('success'),
            Stat::make('Applications', (clone $applicationQuery)->count())
                ->description((clone $applicationQuery)->where('status', JobApplication::STATUS_NEW)->count().' new')
                ->icon(Heroicon::OutlinedInboxStack)
                ->color('warning'),
            Stat::make('Domains', (clone $domainQuery)->count())
                ->description((clone $domainQuery)->where('status', Domain::STATUS_ACTIVE)->count().' live')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->color('info'),
        ];
    }
}
