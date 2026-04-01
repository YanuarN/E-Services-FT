<?php

namespace App\Filament\Resources\ResearchPermissionLetters;

use App\Filament\Resources\ResearchPermissionLetters\Pages\EditResearchPermissionLetter;
use App\Filament\Resources\ResearchPermissionLetters\Pages\ListResearchPermissionLetters;
use App\Filament\Resources\ResearchPermissionLetters\Pages\ViewResearchPermissionLetter;
use App\Filament\Resources\ResearchPermissionLetters\Schemas\ResearchPermissionLetterForm;
use App\Filament\Resources\ResearchPermissionLetters\Schemas\ResearchPermissionLetterInfolist;
use App\Filament\Resources\ResearchPermissionLetters\Tables\ResearchPermissionLettersTable;
use App\Models\ResearchPermissionLetter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResearchPermissionLetterResource extends Resource
{
    protected static ?string $model = ResearchPermissionLetter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    public static function form(Schema $schema): Schema
    {
        return ResearchPermissionLetterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ResearchPermissionLetterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResearchPermissionLettersTable::configure($table);
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
            'index' => ListResearchPermissionLetters::route('/'),
            'view' => ViewResearchPermissionLetter::route('/{record}'),
            'edit' => EditResearchPermissionLetter::route('/{record}/edit'),
        ];
    }
}
