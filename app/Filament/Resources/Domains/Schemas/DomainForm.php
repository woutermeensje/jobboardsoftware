<?php

namespace App\Filament\Resources\Domains\Schemas;

use App\Models\Domain;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DomainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Domain')
                    ->schema([
                        TextInput::make('domain')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('tenant_id')
                            ->relationship('tenant', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Toggle::make('is_primary')
                            ->label('Primary domain'),
                    ])
                    ->columns(2),
                Section::make('Verification')
                    ->schema([
                        Select::make('status')
                            ->options([
                                Domain::STATUS_PENDING => 'Pending',
                                Domain::STATUS_VERIFIED => 'Verified',
                                Domain::STATUS_ACTIVE => 'Active',
                                Domain::STATUS_FAILED => 'Failed',
                            ])
                            ->default(Domain::STATUS_PENDING)
                            ->required()
                            ->native(false),
                        Select::make('ssl_status')
                            ->label('SSL status')
                            ->options([
                                Domain::SSL_PENDING => 'Pending',
                                Domain::SSL_ACTIVE => 'Active',
                                Domain::SSL_FAILED => 'Failed',
                            ])
                            ->default(Domain::SSL_PENDING)
                            ->required()
                            ->native(false),
                        DateTimePicker::make('verified_at'),
                        DateTimePicker::make('ssl_issued_at'),
                        KeyValue::make('verification_payload')
                            ->keyLabel('Record')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
