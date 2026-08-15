<?php

namespace App\Filament\Workspace\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class AddJobType extends WorkspaceMenuPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationParentItem = JobsSettings::class;

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Add job type';

    protected static ?string $title = 'Add job type';

    protected static ?string $slug = 'jobs-settings/job-type';

    protected string $description = 'Add and manage job types for this job board.';
}
