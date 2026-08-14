<?php

namespace App\Filament\Resources\Domains\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DomainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('domain')
                    ->required(),
                TextInput::make('tenant_id')
                    ->required(),
                Toggle::make('is_primary')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('ssl_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('verification_payload'),
                DateTimePicker::make('verified_at'),
                DateTimePicker::make('ssl_issued_at'),
            ]);
    }
}
