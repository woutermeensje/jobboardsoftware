<?php

namespace App\Filament\Workspace\Resources\Environments;

use App\Filament\Workspace\Resources\Environments\Pages\CreateEnvironment;
use App\Filament\Workspace\Resources\Environments\Pages\EditEnvironment;
use App\Filament\Workspace\Resources\Environments\Pages\ListEnvironments;
use App\Filament\Workspace\Resources\Environments\Pages\ViewEnvironment;
use App\Models\Tenant;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use UnitEnum;

class EnvironmentResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Job boards';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'environment';

    protected static ?string $pluralModelLabel = 'environments';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Job board')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state)))
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Subdomain')
                            ->required()
                            ->rule('alpha_dash')
                            ->unique(table: Tenant::class, column: 'slug', ignoreRecord: true)
                            ->suffix('.jobboardsoftware.co')
                            ->maxLength(80)
                            ->disabledOn('edit'),
                    ])
                    ->columns(2),
                Section::make('Branding')
                    ->schema([
                        TextInput::make('settings.brand_name')
                            ->label('Brand name')
                            ->maxLength(255),
                        ColorPicker::make('settings.accent_color')
                            ->label('Accent color')
                            ->default('#2f5f80'),
                        Textarea::make('settings.intro')
                            ->label('Intro')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug')
                    ->label('Subdomain')
                    ->suffix('.jobboardsoftware.co')
                    ->copyable(),
                TextEntry::make('plan')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('billing_status')
                    ->badge(),
                TextEntry::make('trial_ends_at')
                    ->dateTime()
                    ->placeholder('-'),
                KeyValueEntry::make('settings')
                    ->columnSpanFull()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Subdomain')
                    ->formatStateUsing(fn (?string $state): string => $state ? $state.'.jobboardsoftware.co' : '-')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('plan')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        Tenant::STATUS_ACTIVE => 'success',
                        Tenant::STATUS_TRIAL => 'warning',
                        Tenant::STATUS_SUSPENDED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('domains_count')
                    ->label('Domains')
                    ->counts('domains')
                    ->sortable(),
                TextColumn::make('jobs_count')
                    ->label('Jobs')
                    ->counts('jobs')
                    ->sortable(),
                TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Filament::auth()->user();

        if ($user?->isAdmin()) {
            return $query;
        }

        return $query->where('owner_user_id', $user?->id ?? 0);
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
            'index' => ListEnvironments::route('/'),
            'create' => CreateEnvironment::route('/create'),
            'view' => ViewEnvironment::route('/{record}'),
            'edit' => EditEnvironment::route('/{record}/edit'),
        ];
    }
}
