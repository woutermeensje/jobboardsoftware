<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class AddSocials extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $navigationParentItem = Marketing::class;

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Add socials';

    protected static ?string $title = 'Add socials';

    protected static ?string $slug = 'marketing/socials';

    protected string $description = 'Add and manage the social channels for this job board.';
}
