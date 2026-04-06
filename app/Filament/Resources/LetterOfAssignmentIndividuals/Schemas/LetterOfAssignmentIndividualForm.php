<?php

namespace App\Filament\Resources\LetterOfAssignmentIndividuals\Schemas;

use App\Filament\Support\LetterFormComponents;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LetterOfAssignmentIndividualForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Mahasiswa')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        TextInput::make('nim')
                            ->label('NIM')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('departement')
                            ->label('Jurusan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('faculty')
                            ->label('Fakultas')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('assignment')
                            ->label('Penugasan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('place')
                            ->label('Tempat')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date')
                            ->label('Tanggal Kegiatan')
                            ->required(),
                    ])
                    ->columns(2),
                LetterFormComponents::managementSection(),
            ]);
    }
}
