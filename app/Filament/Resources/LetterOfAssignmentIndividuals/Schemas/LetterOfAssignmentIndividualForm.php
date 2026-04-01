<?php

namespace App\Filament\Resources\LetterOfAssignmentIndividuals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LetterOfAssignmentIndividualForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Manajemen Surat')
                    ->description('Kelola status dan metadata penerbitan surat.')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'SUBMITTED' => 'SUBMITTED',
                                'APPROVE' => 'APPROVE',
                                'REJECT' => 'REJECT',
                            ])
                            ->required(),
                        TextInput::make('letter_number')
                            ->label('Nomor Surat')
                            ->maxLength(255),
                        DatePicker::make('letter_date')
                            ->label('Tanggal Surat'),
                    ])
                    ->columns(3),
            ]);
    }
}
