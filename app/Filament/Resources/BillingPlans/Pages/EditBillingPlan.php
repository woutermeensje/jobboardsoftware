<?php

namespace App\Filament\Resources\BillingPlans\Pages;

use App\Filament\Resources\BillingPlans\BillingPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBillingPlan extends EditRecord
{
    protected static string $resource = BillingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
