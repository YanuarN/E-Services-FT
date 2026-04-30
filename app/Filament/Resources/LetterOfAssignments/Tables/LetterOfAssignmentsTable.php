<?php

namespace App\Filament\Resources\LetterOfAssignments\Tables;

use App\Filament\Resources\LetterOfAssignments\LetterOfAssignmentResource;
use App\Filament\Support\LetterTableActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LetterOfAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_names')
                    ->label('Nama Mahasiswa')
                    ->state(fn ($record): string => collect($record->student_list ?? [])
                        ->pluck('nama')
                        ->filter()
                        ->join(', '))
                    ->wrap(),
                TextColumn::make('student_nims')
                    ->label('NIM')
                    ->state(fn ($record): string => collect($record->student_list ?? [])
                        ->pluck('nim')
                        ->filter()
                        ->join(', '))
                    ->wrap(),
                TextColumn::make('place')
                    ->label('Tempat')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('activity')
                    ->label('Kegiatan')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('assigment')
                    ->label('Sebagai')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->searchable()
                    ->wrap()
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
            ->recordUrl(fn ($record): string => LetterOfAssignmentResource::getUrl('edit', ['record' => $record]))
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
