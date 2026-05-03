<?php

namespace App\Filament\Resources\RoomUsageRequests\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\RoomUsageRequests\RoomUsageRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRoomUsageRequest extends ViewRecord
{
    protected static string $resource = RoomUsageRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
