<?php

namespace App\Filament\Resources\BillingPlans\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BillingPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plan')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(40),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('monthly_price_cents')
                            ->label('Monthly price in cents')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('currency')
                            ->required()
                            ->maxLength(3)
                            ->default('eur'),
                        TextInput::make('stripe_price_id')
                            ->label('Stripe price ID')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Entitlements')
                    ->schema([
                        TagsInput::make('features')
                            ->placeholder('Add a feature')
                            ->columnSpanFull(),
                        KeyValue::make('limits')
                            ->keyLabel('Limit')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
