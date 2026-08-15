<?php

namespace App\Filament\Workspace\Resources\Domains;

use App\Filament\Workspace\Resources\Domains\Pages\CreateDomain;
use App\Filament\Workspace\Resources\Domains\Pages\EditDomain;
use App\Filament\Workspace\Resources\Domains\Pages\ListDomains;
use App\Filament\Workspace\Resources\Domains\Pages\ViewDomain;
use App\Models\Domain;
use App\Models\Tenant;
use App\Support\AdminActionNotifier;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'Job boards';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'domain';

    protected static ?string $pluralModelLabel = 'domains';

    protected static ?string $recordTitleAttribute = 'domain';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Domain')
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Environment')
                            ->options(fn () => static::tenantOptions())
                            ->required()
                            ->searchable()
                            ->disabledOn('edit'),
                        TextInput::make('domain')
                            ->required()
                            ->unique(table: Domain::class, column: 'domain', ignoreRecord: true)
                            ->maxLength(255),
                        Toggle::make('is_primary')
                            ->label('Primary domain'),
                    ])
                    ->columns(2),
                Section::make('DNS verification')
                    ->schema([
                        TextInput::make('status')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('ssl_status')
                            ->label('SSL status')
                            ->disabled()
                            ->dehydrated(false),
                        KeyValue::make('verification_payload')
                            ->label('DNS records')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('domain')
                    ->copyable(),
                TextEntry::make('tenant.name')
                    ->label('Environment'),
                IconEntry::make('is_primary')
                    ->label('Primary domain')
                    ->boolean(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('ssl_status')
                    ->label('SSL status')
                    ->badge(),
                TextEntry::make('verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('ssl_issued_at')
                    ->dateTime()
                    ->placeholder('-'),
                KeyValueEntry::make('verification_payload')
                    ->label('DNS records')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('domain')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('tenant.name')
                    ->label('Environment')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        Domain::STATUS_ACTIVE, Domain::STATUS_VERIFIED => 'success',
                        Domain::STATUS_PENDING => 'warning',
                        Domain::STATUS_FAILED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('ssl_status')
                    ->label('SSL')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        Domain::SSL_ACTIVE => 'success',
                        Domain::SSL_PENDING => 'warning',
                        Domain::SSL_FAILED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->recordActions([
                static::checkDnsAction(),
                static::activateSslAction(),
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function checkDnsAction(): Action
    {
        return Action::make('checkDns')
            ->label('Check DNS')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')
            ->action(function (Domain $record): void {
                $verified = $record->checkDnsVerification();

                app(AdminActionNotifier::class)->notify(
                    $verified ? 'Domein DNS geverifieerd' : 'Domein DNS check mislukt',
                    [
                        'tenant_id' => $record->tenant_id,
                        'tenant_naam' => $record->tenant?->name,
                        'domein' => $record->domain,
                        'status' => $record->status,
                        'ssl_status' => $record->ssl_status,
                    ],
                    Filament::auth()->user(),
                );

                $verified
                    ? Notification::make()->title('Domain verified.')->body('SSL can now be prepared.')->success()->send()
                    : Notification::make()->title('DNS records were not found yet.')->body('Check the CNAME or TXT value and try again.')->warning()->send();
            });
    }

    public static function activateSslAction(): Action
    {
        return Action::make('activateSsl')
            ->label('Activate SSL')
            ->icon(Heroicon::OutlinedLockClosed)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Domain $record): bool => in_array($record->status, [Domain::STATUS_VERIFIED, Domain::STATUS_ACTIVE], true))
            ->action(function (Domain $record): void {
                $record->activateSsl();

                app(AdminActionNotifier::class)->notify('SSL geactiveerd', [
                    'tenant_id' => $record->tenant_id,
                    'tenant_naam' => $record->tenant?->name,
                    'domein' => $record->domain,
                    'status' => $record->status,
                    'ssl_status' => $record->ssl_status,
                ], Filament::auth()->user());

                Notification::make()
                    ->title('SSL status has been set to active.')
                    ->success()
                    ->send();
            });
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Filament::auth()->user();

        if ($user?->isAdmin()) {
            return $query;
        }

        return $query->whereHas('tenant', fn (Builder $query) => $query->where('owner_user_id', $user?->id ?? 0));
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDomains::route('/'),
            'create' => CreateDomain::route('/create'),
            'view' => ViewDomain::route('/{record}'),
            'edit' => EditDomain::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function tenantOptions(): array
    {
        $user = Filament::auth()->user();

        return Tenant::query()
            ->when(! $user?->isAdmin(), fn (Builder $query) => $query->where('owner_user_id', $user?->id ?? 0))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
