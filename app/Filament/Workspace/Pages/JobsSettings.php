<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class JobsSettings extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'Jobs settings';

    protected static ?string $title = 'Jobs settings';

    protected static ?string $slug = 'jobs-settings';

    protected string $description = 'Manage the job settings used across this job board.';
}
