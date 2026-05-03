<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Support\AdminAccess;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

abstract class EditLetterRecord extends EditRecord
{
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
