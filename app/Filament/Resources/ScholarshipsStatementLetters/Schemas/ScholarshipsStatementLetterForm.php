<?php

namespace App\Filament\Resources\ScholarshipsStatementLetters\Schemas;

use App\Filament\Support\LetterFormComponents;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScholarshipsStatementLetterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Mahasiswa')
                    ->schema([
                        TextInput::make('student_name')
                            ->label('Nama Mahasiswa')
                            ->required(),
                        TextInput::make('study_program')
                            ->label('Program Studi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nim')
                            ->label('NIM')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('scolarship_name')
                            ->label('Nama Beasiswa')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('scolarship_provider')
                            ->label('Pemberi Beasiswa')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->label('Nomor Telepon')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                LetterFormComponents::managementSection(),
            ]);
    }
}
