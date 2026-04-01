<?php

namespace App\Filament\Resources\LetterOfAssignmentIndividuals;

use App\Filament\Resources\LetterOfAssignmentIndividuals\Pages\EditLetterOfAssignmentIndividual;
use App\Filament\Resources\LetterOfAssignmentIndividuals\Pages\ListLetterOfAssignmentIndividuals;
use App\Filament\Resources\LetterOfAssignmentIndividuals\Pages\ViewLetterOfAssignmentIndividual;
use App\Filament\Resources\LetterOfAssignmentIndividuals\Schemas\LetterOfAssignmentIndividualForm;
use App\Filament\Resources\LetterOfAssignmentIndividuals\Schemas\LetterOfAssignmentIndividualInfolist;
use App\Filament\Resources\LetterOfAssignmentIndividuals\Tables\LetterOfAssignmentIndividualsTable;
use App\Models\LetterOfAssignmentIndividual;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LetterOfAssignmentIndividualResource extends Resource
{
    protected static ?string $model = LetterOfAssignmentIndividual::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    public static function form(Schema $schema): Schema
    {
        return LetterOfAssignmentIndividualForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LetterOfAssignmentIndividualInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LetterOfAssignmentIndividualsTable::configure($table);
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
            'index' => ListLetterOfAssignmentIndividuals::route('/'),
            'view' => ViewLetterOfAssignmentIndividual::route('/{record}'),
            'edit' => EditLetterOfAssignmentIndividual::route('/{record}/edit'),
        ];
    }
}
