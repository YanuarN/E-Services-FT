<?php

namespace App\Filament\Resources\ExamPermissionLetters\Schemas;

use App\Filament\Support\LetterFormComponents;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamPermissionLetterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Surat')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        TextInput::make('nim')
                            ->label('NIM')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('exam')
                            ->label('Jenis Ujian')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('semester')
                            ->label('Semester')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date')
                            ->label('Tanggal Ujian')
                            ->required(),
                    ])
                    ->columns(2),
                LetterFormComponents::managementSection(),
            ]);
    }
}
