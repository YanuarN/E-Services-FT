<?php

namespace App\Filament\Resources\ResearchPermissionLetters\Schemas;

use App\Filament\Support\LetterFormComponents;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ResearchPermissionLetterForm
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
                        TextInput::make('nim')
                            ->label('NIM')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('study_program')
                            ->label('Program Studi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->label('Nomor Telepon')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('company_name')
                            ->label('Instansi')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('company_address')
                            ->label('Alamat Instansi')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                LetterFormComponents::managementSection(),
            ]);
    }
}
