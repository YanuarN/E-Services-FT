<?php

namespace App\Filament\Resources\InternshipLetters\Pages;

use App\Filament\Resources\InternshipLetters\InternshipLetterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInternshipLetter extends ViewRecord
{
    protected static string $resource = InternshipLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
