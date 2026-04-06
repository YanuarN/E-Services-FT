<?php

namespace App\Filament\Resources\InternshipRecommendationLetters\Schemas;

use App\Filament\Support\LetterFormComponents;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InternshipRecommendationLetterForm
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
                        TextInput::make('semester')
                            ->label('Semester')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('ipk')
                            ->label('IPK')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('program_name')
                            ->label('Program')
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
