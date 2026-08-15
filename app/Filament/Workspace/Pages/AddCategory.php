<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class AddCategory extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationParentItem = JobsSettings::class;

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Add categorie';

    protected static ?string $title = 'Add categorie';

    protected static ?string $slug = 'jobs-settings/categorie';

    protected string $description = 'Add and manage categories for jobs on this job board.';
}
