<?php

namespace App\Filament\Resources\BillingPlans\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BillingPlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('key'),
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('monthly_price_cents')
                    ->label('Monthly price')
                    ->money('EUR', divideBy: 100),
                TextEntry::make('currency'),
                TextEntry::make('stripe_price_id')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('features')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                KeyValueEntry::make('limits')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }
}
