<?php

namespace App\Filament\Resources\InternshipRecommendationLetters\Tables;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\InternshipRecommendationLetters\InternshipRecommendationLetterResource;
use App\Filament\Support\LetterTableActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InternshipRecommendationLettersTable
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
                TextColumn::make('program_name')
                    ->label('Program')
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
            ->recordUrl(fn ($record): string => InternshipRecommendationLetterResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                LetterTableActions::accept(),
                LetterTableActions::reject(),
                LetterTableActions::printPdf(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
                ])->visible(fn (): bool => AdminAccess::canMutate()),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
