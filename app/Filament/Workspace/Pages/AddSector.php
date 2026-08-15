<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class AddSector extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationParentItem = JobsSettings::class;

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Add sector';

    protected static ?string $title = 'Add sector';

    protected static ?string $slug = 'jobs-settings/sector';

    protected string $description = 'Add and manage sectors for jobs on this job board.';
}
