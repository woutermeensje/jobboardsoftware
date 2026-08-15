<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Marketing extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Marketing';

    protected static ?string $title = 'Marketing';

    protected static ?string $slug = 'marketing';

    protected string $description = 'Manage the marketing pages and channels for this job board.';
}
