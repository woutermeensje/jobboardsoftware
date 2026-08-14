<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Environment')
                    ->schema([
                        TextInput::make('id')
                            ->label('Tenant ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabledOn('edit'),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('owner_user_id')
                            ->label('Owner')
                            ->relationship('owner', 'name', modifyQueryUsing: fn ($query) => $query->where('role', User::ROLE_TENANT_OWNER))
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                Section::make('Plan and status')
                    ->schema([
                        Select::make('plan')
                            ->options([
                                Tenant::PLAN_STARTER => 'Starter',
                                Tenant::PLAN_GROWTH => 'Growth',
                                Tenant::PLAN_ENTERPRISE => 'Enterprise',
                            ])
                            ->default(Tenant::PLAN_STARTER)
                            ->required()
                            ->native(false),
                        Select::make('status')
                            ->options([
                                Tenant::STATUS_TRIAL => 'Trial',
                                Tenant::STATUS_ACTIVE => 'Active',
                                Tenant::STATUS_SUSPENDED => 'Suspended',
                            ])
                            ->default(Tenant::STATUS_TRIAL)
                            ->required()
                            ->native(false),
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
                            ->default('domain')
                            ->required()
                            ->native(false),
                        DateTimePicker::make('trial_ends_at'),
                        DateTimePicker::make('subscribed_at'),
                        DateTimePicker::make('onboarding_completed_at'),
                    ])
                    ->columns(2),
                Section::make('Settings')
                    ->schema([
                        KeyValue::make('settings')
                            ->keyLabel('Setting')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                        KeyValue::make('data')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
