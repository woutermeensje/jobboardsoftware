<?php

namespace App\Filament\Resources\BillingPlans\Pages;

use App\Filament\Resources\BillingPlans\BillingPlanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBillingPlan extends ViewRecord
{
    protected static string $resource = BillingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
