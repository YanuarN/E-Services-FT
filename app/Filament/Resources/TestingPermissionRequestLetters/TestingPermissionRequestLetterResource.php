<?php

namespace App\Filament\Resources\TestingPermissionRequestLetters;

use App\Filament\Resources\TestingPermissionRequestLetters\Pages\EditTestingPermissionRequestLetter;
use App\Filament\Resources\TestingPermissionRequestLetters\Pages\ListTestingPermissionRequestLetters;
use App\Filament\Resources\TestingPermissionRequestLetters\Pages\ViewTestingPermissionRequestLetter;
use App\Filament\Resources\TestingPermissionRequestLetters\Schemas\TestingPermissionRequestLetterForm;
use App\Filament\Resources\TestingPermissionRequestLetters\Schemas\TestingPermissionRequestLetterInfolist;
use App\Filament\Resources\TestingPermissionRequestLetters\Tables\TestingPermissionRequestLettersTable;
use App\Models\TestingPermissionRequestLetter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TestingPermissionRequestLetterResource extends Resource
{
    protected static ?string $model = TestingPermissionRequestLetter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Surat';

    protected static ?string $navigationLabel = 'Surat Permohonan Izin Pengujian Alat Hasil Penelitian';

    protected static ?string $modelLabel = 'Surat Permohonan Izin Pengujian Alat Hasil Penelitian';

    protected static ?string $pluralModelLabel = 'Surat Permohonan Izin Pengujian Alat Hasil Penelitian';

    public static function form(Schema $schema): Schema
    {
        return TestingPermissionRequestLetterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TestingPermissionRequestLetterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestingPermissionRequestLettersTable::configure($table);
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
            'index' => ListTestingPermissionRequestLetters::route('/'),
            'view' => ViewTestingPermissionRequestLetter::route('/{record}'),
            'edit' => EditTestingPermissionRequestLetter::route('/{record}/edit'),
        ];
    }
}
