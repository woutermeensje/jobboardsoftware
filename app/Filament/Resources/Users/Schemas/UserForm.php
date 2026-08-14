<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                        Select::make('role')
                            ->options([
                                User::ROLE_ADMIN => 'Admin',
                                User::ROLE_TENANT_OWNER => 'Tenant owner',
                                User::ROLE_WERKGEVER => 'Werkgever',
                                User::ROLE_WERKZOEKENDE => 'Werkzoekende',
                            ])
                            ->default(User::ROLE_TENANT_OWNER)
                            ->required()
                            ->native(false),
                        DateTimePicker::make('email_verified_at'),
                    ])
                    ->columns(2),
                Section::make('Profile')
                    ->schema([
                        TextInput::make('first_name')
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->maxLength(255),
                        TextInput::make('company_name')
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->tel()
                            ->maxLength(40),
                        TextInput::make('heard_about_us')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Billing and onboarding')
                    ->schema([
                        Select::make('billing_plan_id')
                            ->relationship('billingPlan', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('billing_status')
                            ->options([
                                'trial' => 'Trial',
                                'active' => 'Active',
                                'past_due' => 'Past due',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('trial')
                            ->required()
                            ->native(false),
                        Select::make('onboarding_step')
                            ->options([
                                'plan' => 'Plan',
                                'domain' => 'Domain',
                                'jobs' => 'Jobs',
                                'complete' => 'Complete',
                            ])
                            ->default('plan')
                            ->required()
                            ->native(false),
                        DateTimePicker::make('onboarding_completed_at'),
                        DateTimePicker::make('trial_ends_at'),
                        TextInput::make('stripe_id')
                            ->maxLength(255),
                        TextInput::make('pm_type')
                            ->label('Payment method')
                            ->maxLength(255),
                        TextInput::make('pm_last_four')
                            ->label('Last four')
                            ->maxLength(4),
                    ])
                    ->columns(2),
            ]);
    }
}
