<?php

namespace App\Filament\Resources\LetterOfAssignments\Schemas;

use App\Filament\Support\LetterFormComponents;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
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
                        DatePicker::make('date')
                            ->label('Tanggal Kegiatan')
                            ->required(),
                        TimePicker::make('time')
                            ->label('Waktu')
                            ->seconds(false),
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
