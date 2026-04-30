<?php

namespace App\Filament\Resources\InternshipLetters\Tables;

use App\Filament\Resources\InternshipLetters\InternshipLetterResource;
use App\Filament\Support\LetterTableActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InternshipLettersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('study_program')
                    ->label('Program Studi')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('company_name')
                    ->label('Instansi')
                    ->searchable()
                    ->wrap(),
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
            ->recordUrl(fn ($record): string => InternshipLetterResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                LetterTableActions::accept(),
                LetterTableActions::reject(),
                LetterTableActions::printPdf(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
