<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class AddOrganizationType extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationParentItem = JobsSettings::class;

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Add organization type';

    protected static ?string $title = 'Add organization type';

    protected static ?string $slug = 'jobs-settings/organization-type';

    protected string $description = 'Add and manage organization types for companies on this job board.';
}
