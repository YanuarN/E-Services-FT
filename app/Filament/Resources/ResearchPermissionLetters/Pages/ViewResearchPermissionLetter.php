<?php

namespace App\Filament\Resources\ResearchPermissionLetters\Pages;

use App\Filament\Resources\ResearchPermissionLetters\ResearchPermissionLetterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewResearchPermissionLetter extends ViewRecord
{
    protected static string $resource = ResearchPermissionLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
