<?php

namespace App\Filament\Resources\RoomUsageRequests\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\RoomUsageRequests\RoomUsageRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRoomUsageRequest extends EditRecord
{
    protected static string $resource = RoomUsageRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
