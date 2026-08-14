<?php

namespace App\Filament\Resources\TenantJobs;

use App\Filament\Resources\TenantJobs\Pages\CreateTenantJob;
use App\Filament\Resources\TenantJobs\Pages\EditTenantJob;
use App\Filament\Resources\TenantJobs\Pages\ListTenantJobs;
use App\Filament\Resources\TenantJobs\Pages\ViewTenantJob;
use App\Filament\Resources\TenantJobs\Schemas\TenantJobForm;
use App\Filament\Resources\TenantJobs\Schemas\TenantJobInfolist;
use App\Filament\Resources\TenantJobs\Tables\TenantJobsTable;
use App\Models\TenantJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TenantJobResource extends Resource
{
    protected static ?string $model = TenantJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationGroup = 'Job boards';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'job';

    protected static ?string $pluralModelLabel = 'jobs';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return TenantJobForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TenantJobInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantJobsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenantJobs::route('/'),
            'create' => CreateTenantJob::route('/create'),
            'view' => ViewTenantJob::route('/{record}'),
            'edit' => EditTenantJob::route('/{record}/edit'),
        ];
    }
}
