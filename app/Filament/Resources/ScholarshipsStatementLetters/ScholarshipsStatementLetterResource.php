<?php

namespace App\Filament\Resources\ScholarshipsStatementLetters;

use App\Filament\Resources\Concerns\RestrictsAdminFakultasMutations;
use App\Filament\Resources\ScholarshipsStatementLetters\Pages\EditScholarshipsStatementLetter;
use App\Filament\Resources\ScholarshipsStatementLetters\Pages\ListScholarshipsStatementLetters;
use App\Filament\Resources\ScholarshipsStatementLetters\Pages\ViewScholarshipsStatementLetter;
use App\Filament\Resources\ScholarshipsStatementLetters\Schemas\ScholarshipsStatementLetterForm;
use App\Filament\Resources\ScholarshipsStatementLetters\Schemas\ScholarshipsStatementLetterInfolist;
use App\Filament\Resources\ScholarshipsStatementLetters\Tables\ScholarshipsStatementLettersTable;
use App\Models\ScholarshipsStatementLetter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScholarshipsStatementLetterResource extends Resource
{
    use RestrictsAdminFakultasMutations;

    protected static ?string $model = ScholarshipsStatementLetter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    protected static ?string $navigationLabel = 'Surat Keterangan Tidak Menerima Beasiswa Lain';

    protected static ?string $modelLabel = 'Surat Keterangan Tidak Menerima Beasiswa Lain';

    protected static ?string $pluralModelLabel = 'Surat Keterangan Tidak Menerima Beasiswa Lain';

    public static function form(Schema $schema): Schema
    {
        return ScholarshipsStatementLetterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ScholarshipsStatementLetterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScholarshipsStatementLettersTable::configure($table);
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
            'index' => ListScholarshipsStatementLetters::route('/'),
            'view' => ViewScholarshipsStatementLetter::route('/{record}'),
            'edit' => EditScholarshipsStatementLetter::route('/{record}/edit'),
        ];
    }
}
