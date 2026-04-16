<?php

namespace App\Filament\Resources\LetterOfAssignments\Schemas;

use App\Filament\Support\LetterFormComponents;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LetterOfAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Kegiatan')
                    ->schema([
                        TextInput::make('date')
                            ->label('Tanggal Kegiatan')
                            ->placeholder('Contoh: Rabu-Jumat, 15-17 Oktober 2025')
                            ->helperText('Isi bebas sesuai format hari/tanggal kegiatan.')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('time')
                            ->label('Waktu')
                            ->placeholder('Contoh: 08.00 WIB s.d. selesai')
                            ->helperText('Isi bebas sesuai format rentang waktu kegiatan.')
                            ->maxLength(255),
                        TextInput::make('place')
                            ->label('Tempat')
                            ->required()
                            ->maxLength(255),
                        LetterFormComponents::memberRepeater('student_list', 'Daftar Mahasiswa'),
                    ])
                    ->columns(2),
                LetterFormComponents::managementSection(),
            ]);
    }
}
