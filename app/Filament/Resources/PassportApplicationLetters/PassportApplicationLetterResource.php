<?php

namespace App\Filament\Resources\PassportApplicationLetters;

use App\Filament\Resources\PassportApplicationLetters\Pages\EditPassportApplicationLetter;
use App\Filament\Resources\PassportApplicationLetters\Pages\ListPassportApplicationLetters;
use App\Filament\Resources\PassportApplicationLetters\Pages\ViewPassportApplicationLetter;
use App\Filament\Resources\PassportApplicationLetters\Schemas\PassportApplicationLetterForm;
use App\Filament\Resources\PassportApplicationLetters\Schemas\PassportApplicationLetterInfolist;
use App\Filament\Resources\PassportApplicationLetters\Tables\PassportApplicationLettersTable;
use App\Models\PassportApplicationLetter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PassportApplicationLetterResource extends Resource
{
    protected static ?string $model = PassportApplicationLetter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    public static function form(Schema $schema): Schema
    {
        return PassportApplicationLetterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PassportApplicationLetterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PassportApplicationLettersTable::configure($table);
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
            'index' => ListPassportApplicationLetters::route('/'),
            'view' => ViewPassportApplicationLetter::route('/{record}'),
            'edit' => EditPassportApplicationLetter::route('/{record}/edit'),
        ];
    }
}
