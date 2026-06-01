<?php

namespace App\Filament\Resources\RoomUsageRequests\Tables;

use App\Filament\Support\AdminAccess;
use App\Filament\Support\LetterTableActions;
use App\Filament\Support\LetterTableColumns;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomUsageRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('student_name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable(),
                TextColumn::make('activity_name')
                    ->label('Kegiatan')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('resolved_room_name')
                    ->label('Ruangan')
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('slot_summary')
                    ->label('Detail Slot')
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('start_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'APPROVE', 'APPROVED' => 'success',
                        'REJECT', 'REJECTED' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                LetterTableColumns::evidence(),
                TextColumn::make('letter_number')
                    ->label('Nomor Surat')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pdf_path')
                    ->label('PDF')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Tersedia' : '-')
                    ->badge()
                    ->color(fn (?string $state): string => filled($state) ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'PENDING',
                        'APPROVED' => 'APPROVED',
                        'REJECTED' => 'REJECTED',
                    ]),
            ])
            ->recordActions([
                LetterTableActions::accept(),
                LetterTableActions::reject(),
                LetterTableActions::printPdf(),
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
