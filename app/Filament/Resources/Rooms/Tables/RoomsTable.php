<?php

namespace App\Filament\Resources\Rooms\Tables;

use App\Filament\Support\AdminAccess;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Ruang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
                ])->visible(fn (): bool => AdminAccess::canMutate()),
            ])
            ->defaultSort('name');
    }
}
