<?php

namespace App\Filament\Workspace\Resources\Jobs;

use App\Filament\Workspace\Resources\Jobs\Pages\CreateJob;
use App\Filament\Workspace\Resources\Jobs\Pages\EditJob;
use App\Filament\Workspace\Resources\Jobs\Pages\ListJobs;
use App\Filament\Workspace\Resources\Jobs\Pages\ViewJob;
use App\Models\Tenant;
use App\Models\TenantJob;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class JobResource extends Resource
{
    protected static ?string $model = TenantJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'job';

    protected static ?string $pluralModelLabel = 'jobs';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Job')
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Environment')
                            ->options(fn () => static::tenantOptions())
                            ->required()
                            ->searchable()
                            ->disabledOn('edit'),
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state)))
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->rule('alpha_dash')
                            ->unique(
                                table: TenantJob::class,
                                column: 'slug',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('tenant_id', $get('tenant_id')),
                            )
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
                            ->rows(12)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant.name')
                    ->label('Environment'),
                TextEntry::make('title'),
                TextEntry::make('slug'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('department')
                    ->placeholder('-'),
                TextEntry::make('location')
                    ->placeholder('-'),
                TextEntry::make('employment_type')
                    ->placeholder('-'),
                TextEntry::make('salary_range')
                    ->placeholder('-'),
                TextEntry::make('intro')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('closes_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('tenant.name')
                    ->label('Environment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        TenantJob::STATUS_PUBLISHED => 'success',
                        TenantJob::STATUS_DRAFT => 'warning',
                        TenantJob::STATUS_CLOSED => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        TenantJob::STATUS_DRAFT => 'Draft',
                        TenantJob::STATUS_PUBLISHED => 'Published',
                        TenantJob::STATUS_CLOSED => 'Closed',
                    ]),
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
            'index' => ListJobs::route('/'),
            'create' => CreateJob::route('/create'),
            'view' => ViewJob::route('/{record}'),
            'edit' => EditJob::route('/{record}/edit'),
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
