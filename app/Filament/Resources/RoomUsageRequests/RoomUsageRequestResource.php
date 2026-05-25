<?php

namespace App\Filament\Resources\RoomUsageRequests;

use App\Filament\Resources\Concerns\RestrictsAdminFakultasMutations;
use App\Filament\Resources\RoomUsageRequests\Pages\CreateRoomUsageRequest;
use App\Filament\Resources\RoomUsageRequests\Pages\EditRoomUsageRequest;
use App\Filament\Resources\RoomUsageRequests\Pages\ListRoomUsageRequests;
use App\Filament\Resources\RoomUsageRequests\Pages\ViewRoomUsageRequest;
use App\Filament\Resources\RoomUsageRequests\Schemas\RoomUsageRequestForm;
use App\Filament\Resources\RoomUsageRequests\Schemas\RoomUsageRequestInfolist;
use App\Filament\Resources\RoomUsageRequests\Tables\RoomUsageRequestsTable;
use App\Models\RoomUsageRequest;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RoomUsageRequestResource extends Resource
{
    use RestrictsAdminFakultasMutations;

    protected static ?string $model = RoomUsageRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Ruang';

    protected static ?string $navigationLabel = 'Peminjaman Ruang';

    protected static ?string $modelLabel = 'Peminjaman Ruang';

    protected static ?string $pluralModelLabel = 'Peminjaman Ruang';

    public static function form(Schema $schema): Schema
    {
        return RoomUsageRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoomUsageRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomUsageRequestsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['room', 'slots.room']);
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
            'index' => ListRoomUsageRequests::route('/'),
            'create' => CreateRoomUsageRequest::route('/create'),
            'view' => ViewRoomUsageRequest::route('/{record}'),
            'edit' => EditRoomUsageRequest::route('/{record}/edit'),
        ];
    }
}
