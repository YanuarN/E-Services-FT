<?php

namespace App\Filament\Resources\ExamPermissionLetters\Schemas;

use App\Filament\Support\LetterFormComponents;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                        TextInput::make('company_name')
                            ->label('Nama Perusahaan')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('company_address')
                            ->label('Alamat Perusahaan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        LetterFormComponents::memberRepeater('group_member', 'Anggota Kelompok'),
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
