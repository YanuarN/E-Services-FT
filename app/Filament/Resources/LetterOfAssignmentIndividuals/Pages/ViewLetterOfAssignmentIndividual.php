<?php

namespace App\Filament\Resources\LetterOfAssignmentIndividuals\Pages;

use App\Filament\Resources\LetterOfAssignmentIndividuals\LetterOfAssignmentIndividualResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLetterOfAssignmentIndividual extends ViewRecord
{
    protected static string $resource = LetterOfAssignmentIndividualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
