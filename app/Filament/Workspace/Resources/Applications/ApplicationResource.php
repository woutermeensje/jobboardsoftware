<?php

namespace App\Filament\Workspace\Resources\Applications;

use App\Filament\Workspace\Resources\Applications\Pages\EditApplication;
use App\Filament\Workspace\Resources\Applications\Pages\ListApplications;
use App\Filament\Workspace\Resources\Applications\Pages\ViewApplication;
use App\Models\JobApplication;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|UnitEnum|null $navigationGroup = 'Recruitment';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'application';

    protected static ?string $pluralModelLabel = 'applications';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Candidate')
                    ->schema([
                        TextInput::make('name')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('email')
                            ->label('Email address')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('phone')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('cv_path')
                            ->label('CV path')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                Section::make('Application')
                    ->schema([
                        Select::make('status')
                            ->options([
                                JobApplication::STATUS_NEW => 'New',
                                JobApplication::STATUS_REVIEWED => 'Reviewed',
                                JobApplication::STATUS_REJECTED => 'Rejected',
                                JobApplication::STATUS_HIRED => 'Hired',
                            ])
                            ->required()
                            ->native(false),
                        Textarea::make('motivation')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(8)
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
                TextEntry::make('job.title')
                    ->label('Job'),
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->copyable(),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('cv_path')
                    ->label('CV')
                    ->placeholder('-')
                    ->copyable(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('motivation')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('job.title')
                    ->label('Job')
                    ->searchable()
                    ->sortable()
                    ->limit(45),
                TextColumn::make('tenant.name')
                    ->label('Environment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        JobApplication::STATUS_NEW => 'warning',
                        JobApplication::STATUS_REVIEWED => 'info',
                        JobApplication::STATUS_HIRED => 'success',
                        JobApplication::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        JobApplication::STATUS_NEW => 'New',
                        JobApplication::STATUS_REVIEWED => 'Reviewed',
                        JobApplication::STATUS_REJECTED => 'Rejected',
                        JobApplication::STATUS_HIRED => 'Hired',
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

    public static function canCreate(): bool
    {
        return false;
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
            'index' => ListApplications::route('/'),
            'view' => ViewApplication::route('/{record}'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}
