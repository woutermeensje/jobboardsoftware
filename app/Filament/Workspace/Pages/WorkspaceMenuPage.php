<?php

namespace App\Filament\Workspace\Pages;

use Filament\Pages\Page;

abstract class WorkspaceMenuPage extends Page
{
    protected string $view = 'filament.workspace.pages.placeholder';

    protected string $description = 'This page is ready to be configured.';

    public function getPageDescription(): string
    {
        return $this->description;
    }
}
