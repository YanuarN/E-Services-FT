<?php

namespace App\Filament\Resources\ResearchDataRequestLetters\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\ResearchDataRequestLetters\ResearchDataRequestLetterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewResearchDataRequestLetter extends ViewRecord
{
    protected static string $resource = ResearchDataRequestLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
