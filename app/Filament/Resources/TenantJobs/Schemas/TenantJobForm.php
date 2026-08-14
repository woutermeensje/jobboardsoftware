<?php

namespace App\Filament\Resources\TenantJobs\Schemas;

use App\Models\TenantJob;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantJobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Job')
                    ->schema([
                        Select::make('tenant_id')
                            ->relationship('tenant', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                TenantJob::STATUS_DRAFT => 'Draft',
                                TenantJob::STATUS_PUBLISHED => 'Published',
                                TenantJob::STATUS_CLOSED => 'Closed',
                            ])
                            ->default(TenantJob::STATUS_DRAFT)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
                Section::make('Details')
                    ->schema([
                        TextInput::make('department')
                            ->maxLength(255),
                        TextInput::make('location')
                            ->maxLength(255),
                        TextInput::make('employment_type')
                            ->maxLength(255),
                        TextInput::make('salary_range')
                            ->maxLength(255),
                        DateTimePicker::make('published_at'),
                        DateTimePicker::make('closes_at'),
                    ])
                    ->columns(2),
                Section::make('Content')
                    ->schema([
                        Textarea::make('intro')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(10)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
