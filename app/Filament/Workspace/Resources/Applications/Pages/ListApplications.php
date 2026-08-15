<?php

namespace App\Filament\Workspace\Resources\Applications\Pages;

use App\Filament\Workspace\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;
}
