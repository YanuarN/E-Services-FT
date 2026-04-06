<?php

namespace App\Filament\Resources\ExamPermissionLetters\Tables;

use App\Filament\Resources\ExamPermissionLetters\ExamPermissionLetterResource;
use App\Filament\Support\LetterTableActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamPermissionLettersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exam')
                    ->label('Jenis Ujian')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('semester')
                    ->label('Semester')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'APPROVE' => 'success',
                        'REJECT' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'SUBMITTED' => 'SUBMITTED',
                        'APPROVE' => 'APPROVE',
                        'REJECT' => 'REJECT',
                    ]),
            ])
            ->recordUrl(fn ($record): string => ExamPermissionLetterResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                LetterTableActions::accept(),
                LetterTableActions::reject(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
