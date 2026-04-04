<?php

namespace App\Filament\Resources\ResearchDataRequestLetters;

use App\Filament\Resources\ResearchDataRequestLetters\Pages\EditResearchDataRequestLetter;
use App\Filament\Resources\ResearchDataRequestLetters\Pages\ListResearchDataRequestLetters;
use App\Filament\Resources\ResearchDataRequestLetters\Pages\ViewResearchDataRequestLetter;
use App\Filament\Resources\ResearchDataRequestLetters\Schemas\ResearchDataRequestLetterForm;
use App\Filament\Resources\ResearchDataRequestLetters\Schemas\ResearchDataRequestLetterInfolist;
use App\Filament\Resources\ResearchDataRequestLetters\Tables\ResearchDataRequestLettersTable;
use App\Models\ResearchDataRequestLetter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResearchDataRequestLetterResource extends Resource
{
    protected static ?string $model = ResearchDataRequestLetter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    protected static ?string $navigationLabel = 'Surat Permohonan Data Untuk Penelitian';

    protected static ?string $modelLabel = 'Surat Permohonan Data Untuk Penelitian';

    protected static ?string $pluralModelLabel = 'Surat Permohonan Data Untuk Penelitian';

    public static function form(Schema $schema): Schema
    {
        return ResearchDataRequestLetterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ResearchDataRequestLetterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResearchDataRequestLettersTable::configure($table);
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
            'index' => ListResearchDataRequestLetters::route('/'),
            'view' => ViewResearchDataRequestLetter::route('/{record}'),
            'edit' => EditResearchDataRequestLetter::route('/{record}/edit'),
        ];
    }
}
