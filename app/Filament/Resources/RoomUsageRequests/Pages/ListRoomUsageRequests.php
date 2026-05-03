<?php

namespace App\Filament\Resources\RoomUsageRequests\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\RoomUsageRequests\RoomUsageRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoomUsageRequests extends ListRecords
{
    protected static string $resource = RoomUsageRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
