<?php

namespace App\Filament\Resources\Rooms\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
