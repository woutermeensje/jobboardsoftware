<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TenantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('owner.name')
                    ->label('Owner')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('plan')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('billing_status')
                    ->badge(),
                TextEntry::make('onboarding_step')
                    ->badge(),
                TextEntry::make('onboarding_completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('trial_ends_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('subscribed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                KeyValueEntry::make('settings')
                    ->placeholder('-')
                    ->columnSpanFull(),
                KeyValueEntry::make('data')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }
}
