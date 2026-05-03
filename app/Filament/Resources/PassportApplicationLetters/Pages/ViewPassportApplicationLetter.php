<?php

namespace App\Filament\Resources\PassportApplicationLetters\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\PassportApplicationLetters\PassportApplicationLetterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPassportApplicationLetter extends ViewRecord
{
    protected static string $resource = PassportApplicationLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
