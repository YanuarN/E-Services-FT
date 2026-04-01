<?php

namespace App\Filament\Resources\LetterOfAssignmentIndividuals\Pages;

use App\Filament\Resources\LetterOfAssignmentIndividuals\LetterOfAssignmentIndividualResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLetterOfAssignmentIndividual extends EditRecord
{
    protected static string $resource = LetterOfAssignmentIndividualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
