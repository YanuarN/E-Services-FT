<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Ruang')
                    ->description('Kelola data master ruang yang dapat dipinjam.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Ruang')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('capacity')
                            ->label('Kapasitas')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(2),
            ]);
    }
}
