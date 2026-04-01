<?php

namespace App\Filament\Resources\InternshipLetters\Pages;

use App\Filament\Resources\InternshipLetters\InternshipLetterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInternshipLetter extends EditRecord
{
    protected static string $resource = InternshipLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
