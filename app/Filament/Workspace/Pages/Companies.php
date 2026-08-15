<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Companies extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?int $navigationSort = 60;

    protected static ?string $navigationLabel = 'Companies';

    protected static ?string $title = 'Companies';

    protected static ?string $slug = 'companies';

    protected string $description = 'Manage companies for this job board.';
}
