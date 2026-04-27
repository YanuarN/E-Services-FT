<?php

namespace App\Filament\Resources\AdminWhatsappContacts;

use App\Filament\Resources\AdminWhatsappContacts\Pages\CreateAdminWhatsappContact;
use App\Filament\Resources\AdminWhatsappContacts\Pages\EditAdminWhatsappContact;
use App\Filament\Resources\AdminWhatsappContacts\Pages\ListAdminWhatsappContacts;
use App\Filament\Resources\AdminWhatsappContacts\Schemas\AdminWhatsappContactForm;
use App\Filament\Resources\AdminWhatsappContacts\Tables\AdminWhatsappContactsTable;
use App\Models\AdminWhatsappContact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminWhatsappContactResource extends Resource
{
    protected static ?string $model = AdminWhatsappContact::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Kontak WhatsApp Admin';

    protected static ?string $modelLabel = 'Kontak WhatsApp Admin';

    protected static ?string $pluralModelLabel = 'Kontak WhatsApp Admin';

    public static function form(Schema $schema): Schema
    {
        return AdminWhatsappContactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminWhatsappContactsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereKey(1);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminWhatsappContacts::route('/'),
            'edit' => EditAdminWhatsappContact::route('/{record}/edit'),
        ];
    }
}
