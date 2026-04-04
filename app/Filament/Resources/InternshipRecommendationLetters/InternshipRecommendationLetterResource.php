<?php

namespace App\Filament\Resources\InternshipRecommendationLetters;

use App\Filament\Resources\InternshipRecommendationLetters\Pages\EditInternshipRecommendationLetter;
use App\Filament\Resources\InternshipRecommendationLetters\Pages\ListInternshipRecommendationLetters;
use App\Filament\Resources\InternshipRecommendationLetters\Pages\ViewInternshipRecommendationLetter;
use App\Filament\Resources\InternshipRecommendationLetters\Schemas\InternshipRecommendationLetterForm;
use App\Filament\Resources\InternshipRecommendationLetters\Schemas\InternshipRecommendationLetterInfolist;
use App\Filament\Resources\InternshipRecommendationLetters\Tables\InternshipRecommendationLettersTable;
use App\Models\InternshipRecommendationLetter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InternshipRecommendationLetterResource extends Resource
{
    protected static ?string $model = InternshipRecommendationLetter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    protected static ?string $navigationLabel = 'Surat Rekomendasi Magang Mandiri';

    protected static ?string $modelLabel = 'Surat Rekomendasi Magang Mandiri';

    protected static ?string $pluralModelLabel = 'Surat Rekomendasi Magang Mandiri';

    public static function form(Schema $schema): Schema
    {
        return InternshipRecommendationLetterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InternshipRecommendationLetterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InternshipRecommendationLettersTable::configure($table);
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
            'index' => ListInternshipRecommendationLetters::route('/'),
            'view' => ViewInternshipRecommendationLetter::route('/{record}'),
            'edit' => EditInternshipRecommendationLetter::route('/{record}/edit'),
        ];
    }
}
