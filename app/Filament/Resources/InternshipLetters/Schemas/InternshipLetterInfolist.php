<?php

namespace App\Filament\Resources\InternshipLetters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InternshipLetterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Surat')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'APPROVE' => 'success',
                                'REJECT' => 'danger',
                                default => 'warning',
                            }),
                        TextEntry::make('letter_number')
                            ->label('Nomor Surat')
                            ->placeholder('-'),
                        TextEntry::make('letter_date')
                            ->label('Tanggal Surat')
                            ->date('d M Y')
                            ->placeholder('-'),
                        TextEntry::make('public_token')
                            ->label('Public Token')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(3),
            ]);
    }
}
