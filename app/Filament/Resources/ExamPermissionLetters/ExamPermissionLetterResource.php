<?php

namespace App\Filament\Resources\ExamPermissionLetters;

use App\Filament\Resources\ExamPermissionLetters\Pages\EditExamPermissionLetter;
use App\Filament\Resources\ExamPermissionLetters\Pages\ListExamPermissionLetters;
use App\Filament\Resources\ExamPermissionLetters\Pages\ViewExamPermissionLetter;
use App\Filament\Resources\ExamPermissionLetters\Schemas\ExamPermissionLetterForm;
use App\Filament\Resources\ExamPermissionLetters\Schemas\ExamPermissionLetterInfolist;
use App\Filament\Resources\ExamPermissionLetters\Tables\ExamPermissionLettersTable;
use App\Models\ExamPermissionLetter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExamPermissionLetterResource extends Resource
{
    protected static ?string $model = ExamPermissionLetter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    protected static ?string $navigationLabel = 'Surat Izin Untuk Mengikuti Ujian (Khusus Mahasiswa Kerja Praktek)';

    protected static ?string $modelLabel = 'Surat Izin Untuk Mengikuti Ujian (Khusus Mahasiswa Kerja Praktek)';

    protected static ?string $pluralModelLabel = 'Surat Izin Untuk Mengikuti Ujian (Khusus Mahasiswa Kerja Praktek)';

    public static function form(Schema $schema): Schema
    {
        return ExamPermissionLetterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamPermissionLetterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamPermissionLettersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamPermissionLetters::route('/'),
            'view' => ViewExamPermissionLetter::route('/{record}'),
            'edit' => EditExamPermissionLetter::route('/{record}/edit'),
        ];
    }
}
