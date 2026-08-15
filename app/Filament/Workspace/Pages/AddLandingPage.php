<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class AddLandingPage extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentPlus;

    protected static ?string $navigationParentItem = Marketing::class;

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Add landingpagina';

    protected static ?string $title = 'Add landingpagina';

    protected static ?string $slug = 'marketing/landingpagina';

    protected string $description = 'Create and manage the landing page for this job board.';
}
