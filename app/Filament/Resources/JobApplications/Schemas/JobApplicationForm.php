<?php

namespace App\Filament\Resources\JobApplications\Schemas;

use App\Models\JobApplication;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Candidate')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                JobApplication::STATUS_NEW => 'New',
                                JobApplication::STATUS_REVIEWED => 'Reviewed',
                                JobApplication::STATUS_REJECTED => 'Rejected',
                                JobApplication::STATUS_HIRED => 'Hired',
                            ])
                            ->default(JobApplication::STATUS_NEW)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
                Section::make('Job board')
                    ->schema([
                        Select::make('tenant_id')
                            ->relationship('tenant', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('tenant_job_id')
                            ->label('Job')
                            ->relationship('job', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('cv_path')
                            ->label('CV path')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Motivation')
                    ->schema([
                        Textarea::make('motivation')
                            ->rows(8)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
