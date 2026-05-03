<?php

namespace App\Filament\Resources\InternshipLetters;

use App\Filament\Resources\Concerns\RestrictsAdminFakultasMutations;
use App\Filament\Resources\InternshipLetters\Pages\EditInternshipLetter;
use App\Filament\Resources\InternshipLetters\Pages\ListInternshipLetters;
use App\Filament\Resources\InternshipLetters\Pages\ViewInternshipLetter;
use App\Filament\Resources\InternshipLetters\Schemas\InternshipLetterForm;
use App\Filament\Resources\InternshipLetters\Schemas\InternshipLetterInfolist;
use App\Filament\Resources\InternshipLetters\Tables\InternshipLettersTable;
use App\Models\InternshipLetter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InternshipLetterResource extends Resource
{
    use RestrictsAdminFakultasMutations;

    protected static ?string $model = InternshipLetter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    protected static ?string $navigationLabel = 'Surat Permohonan Praktek Kerja Nyata (PKN)';

    protected static ?string $modelLabel = 'Surat Permohonan Praktek Kerja Nyata (PKN)';

    protected static ?string $pluralModelLabel = 'Surat Permohonan Praktek Kerja Nyata (PKN)';

    public static function form(Schema $schema): Schema
    {
        return InternshipLetterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InternshipLetterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InternshipLettersTable::configure($table);
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
            'index' => ListInternshipLetters::route('/'),
            'view' => ViewInternshipLetter::route('/{record}'),
            'edit' => EditInternshipLetter::route('/{record}/edit'),
        ];
    }
}
