<?php

namespace App\Filament\Resources\LetterTemplates\Schemas;

use App\Models\LetterTemplate;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Route;

class LetterTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Template Surat')
                    ->schema([
                        TextEntry::make('letter_type')
                            ->label('Jenis Surat')
                            ->formatStateUsing(fn (string $state): string => LetterTemplate::LETTER_TYPES[$state] ?? $state),
                    ])
                    ->columns(1),

                Section::make('Dokumen')
                    ->schema([
                        TextEntry::make('document_path')
                            ->label('Path Dokumen')
                            ->url(fn ($record) => $record?->document_path
                                ? (Route::has('filament.admin.resources.letter-templates.download')
                                    ? route('filament.admin.resources.letter-templates.download', $record)
                                    : null)
                                : null
                            )
                            ->openUrlInNewTab()
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
