<?php

namespace App\Filament\Resources\LetterOfAssignments\Pages;

use App\Filament\Resources\LetterOfAssignments\LetterOfAssignmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLetterOfAssignment extends ViewRecord
{
    protected static string $resource = LetterOfAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
