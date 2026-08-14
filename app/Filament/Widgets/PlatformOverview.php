<?php

namespace App\Filament\Widgets;

use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\Tenant;
use App\Models\TenantJob;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Platform overview';

    protected ?string $description = 'A quick pulse check for JobBoardSoftware.';

    protected function getStats(): array
    {
        return [
            Stat::make('Users', User::count())
                ->description(User::where('role', User::ROLE_TENANT_OWNER)->count().' tenant owners')
                ->icon(Heroicon::OutlinedUsers)
                ->color('primary'),
            Stat::make('Environments', Tenant::count())
                ->description(Tenant::where('status', Tenant::STATUS_ACTIVE)->count().' active')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('info'),
            Stat::make('Domains', Domain::count())
                ->description(Domain::where('status', Domain::STATUS_ACTIVE)->count().' active')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->color('success'),
            Stat::make('Jobs', TenantJob::count())
                ->description(TenantJob::where('status', TenantJob::STATUS_PUBLISHED)->count().' published')
                ->icon(Heroicon::OutlinedBriefcase)
                ->color('warning'),
            Stat::make('Applications', JobApplication::count())
                ->description(JobApplication::where('status', JobApplication::STATUS_NEW)->count().' new')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('gray'),
        ];
    }
}
