<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class AddCompany extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static ?string $navigationParentItem = Companies::class;

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Add company';

    protected static ?string $title = 'Add company';

    protected static ?string $slug = 'companies/create';

    protected string $description = 'Add a company for this job board.';
}
