<?php

namespace App\Filament\Resources\Domains\Tables;

use App\Models\Domain;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DomainsTable
{
    public static function configure(Table $table): Table
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
                    ->boolean(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        Domain::STATUS_ACTIVE, Domain::STATUS_VERIFIED => 'success',
                        Domain::STATUS_PENDING => 'warning',
                        Domain::STATUS_FAILED => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ssl_issued_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Domain::STATUS_PENDING => 'Pending',
                        Domain::STATUS_VERIFIED => 'Verified',
                        Domain::STATUS_ACTIVE => 'Active',
                        Domain::STATUS_FAILED => 'Failed',
                    ]),
                SelectFilter::make('ssl_status')
                    ->label('SSL status')
                    ->options([
                        Domain::SSL_PENDING => 'Pending',
                        Domain::SSL_ACTIVE => 'Active',
                        Domain::SSL_FAILED => 'Failed',
                    ]),
                TernaryFilter::make('is_primary')
                    ->label('Primary domain'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
