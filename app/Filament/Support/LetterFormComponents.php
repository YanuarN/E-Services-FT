<?php

namespace App\Filament\Support;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class LetterFormComponents
{
    public static function managementSection(): Section
    {
        return Section::make('Manajemen Surat')
            ->description('Kelola status dan metadata penerbitan surat.')
            ->schema([
                Select::make('status')
                    ->label('Status')
                    ->options(self::statusOptions())
                    ->required(),
                TextInput::make('letter_number')
                    ->label('Nomor Surat')
                    ->maxLength(255),
                DatePicker::make('letter_date')
                    ->label('Tanggal Surat'),
            ])
            ->columns(3);
    }

    public static function memberRepeater(string $name, string $label): Repeater
    {
        return Repeater::make($name)
            ->label($label)
            ->schema([
                TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('nim')
                    ->label('NIM')
                    ->required()
                    ->maxLength(255),
                TextInput::make('program_studi')
                    ->label('Program Studi')
                    ->maxLength(255),
                TextInput::make('nomor_telepon')
                    ->label('Nomor HP')
                    ->tel()
                    ->maxLength(255),
            ])
            ->columns(4)
            ->defaultItems(0)
            ->reorderable(false)
            ->columnSpanFull();
    }

    public static function statusOptions(): array
    {
        return [
            'SUBMITTED' => 'SUBMITTED',
            'APPROVE' => 'APPROVE',
            'REJECT' => 'REJECT',
        ];
    }
}
