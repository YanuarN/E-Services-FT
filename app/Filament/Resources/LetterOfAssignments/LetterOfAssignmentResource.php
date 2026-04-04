<?php

namespace App\Filament\Resources\LetterOfAssignments;

use App\Filament\Resources\LetterOfAssignments\Pages\EditLetterOfAssignment;
use App\Filament\Resources\LetterOfAssignments\Pages\ListLetterOfAssignments;
use App\Filament\Resources\LetterOfAssignments\Pages\ViewLetterOfAssignment;
use App\Filament\Resources\LetterOfAssignments\Schemas\LetterOfAssignmentForm;
use App\Filament\Resources\LetterOfAssignments\Schemas\LetterOfAssignmentInfolist;
use App\Filament\Resources\LetterOfAssignments\Tables\LetterOfAssignmentsTable;
use App\Models\LetterOfAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LetterOfAssignmentResource extends Resource
{
    protected static ?string $model = LetterOfAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    protected static ?string $navigationLabel = 'Surat Tugas Mahasiswa (Kolektif/Kelompok)';

    protected static ?string $modelLabel = 'Surat Tugas Mahasiswa (Kolektif/Kelompok)';

    protected static ?string $pluralModelLabel = 'Surat Tugas Mahasiswa (Kolektif/Kelompok)';

    public static function form(Schema $schema): Schema
    {
        return LetterOfAssignmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LetterOfAssignmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LetterOfAssignmentsTable::configure($table);
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
            'index' => ListLetterOfAssignments::route('/'),
            'view' => ViewLetterOfAssignment::route('/{record}'),
            'edit' => EditLetterOfAssignment::route('/{record}/edit'),
        ];
    }
}
