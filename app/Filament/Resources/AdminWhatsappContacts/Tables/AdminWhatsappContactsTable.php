<?php

namespace App\Filament\Resources\AdminWhatsappContacts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminWhatsappContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('whatsapp_number')
                    ->label('Nomor WhatsApp')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
