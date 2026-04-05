<?php

namespace App\Filament\Resources\LetterTemplates\Schemas;

use App\Models\LetterTemplate;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LetterTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Template Surat')
                    ->description('Isi data template surat yang akan digunakan.')
                    ->schema([
                        Select::make('letter_type')
                            ->label('Jenis Surat')
                            ->options(LetterTemplate::LETTER_TYPES)
                            ->required()
                            ->native(false)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(1),

                Section::make('Dokumen Template')
                    ->description('Upload file template DOCX yang akan diproses menjadi surat PDF.')
                    ->schema([
                        FileUpload::make('document_path')
                            ->label('File Template')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->directory('letter-templates')
                            ->visibility('private')
                            ->maxSize(10240), // 10 MB
                    ]),
            ]);
    }
}
