<?php

namespace App\Filament\Resources\LetterOfAssignmentIndividuals\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\LetterOfAssignmentIndividuals\LetterOfAssignmentIndividualResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLetterOfAssignmentIndividual extends ViewRecord
{
    protected static string $resource = LetterOfAssignmentIndividualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
