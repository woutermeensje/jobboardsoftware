<?php

namespace App\Filament\Resources\BillingPlans;

use App\Filament\Resources\BillingPlans\Pages\CreateBillingPlan;
use App\Filament\Resources\BillingPlans\Pages\EditBillingPlan;
use App\Filament\Resources\BillingPlans\Pages\ListBillingPlans;
use App\Filament\Resources\BillingPlans\Pages\ViewBillingPlan;
use App\Filament\Resources\BillingPlans\Schemas\BillingPlanForm;
use App\Filament\Resources\BillingPlans\Schemas\BillingPlanInfolist;
use App\Filament\Resources\BillingPlans\Tables\BillingPlansTable;
use App\Models\BillingPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BillingPlanResource extends Resource
{
    protected static ?string $model = BillingPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'billing plan';

    protected static ?string $pluralModelLabel = 'billing plans';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BillingPlanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BillingPlanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillingPlansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBillingPlans::route('/'),
            'create' => CreateBillingPlan::route('/create'),
            'view' => ViewBillingPlan::route('/{record}'),
            'edit' => EditBillingPlan::route('/{record}/edit'),
        ];
    }
}
