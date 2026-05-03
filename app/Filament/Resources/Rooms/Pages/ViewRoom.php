<?php

namespace App\Filament\Resources\Rooms\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRoom extends ViewRecord
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
