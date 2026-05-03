<?php

namespace App\Filament\Resources\LetterTemplates\Tables;

use App\Filament\Support\AdminAccess;
use App\Models\LetterTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LetterTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('letter_type')
                    ->label('Jenis Surat')
                    ->formatStateUsing(fn (string $state): string => LetterTemplate::LETTER_TYPES[$state] ?? $state)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document_path')
                    ->label('Dokumen')
                    ->limit(40)
                    ->toggleable(),

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
            ->defaultSort('created_at', 'desc');
    }
}
