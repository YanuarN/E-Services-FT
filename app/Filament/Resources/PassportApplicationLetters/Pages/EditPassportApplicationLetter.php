<?php

namespace App\Filament\Resources\PassportApplicationLetters\Pages;

use App\Filament\Resources\PassportApplicationLetters\PassportApplicationLetterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPassportApplicationLetter extends EditRecord
{
    protected static string $resource = PassportApplicationLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
