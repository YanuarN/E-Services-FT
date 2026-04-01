<?php

namespace App\Filament\Resources\LetterOfAssignments\Pages;

use App\Filament\Resources\LetterOfAssignments\LetterOfAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLetterOfAssignment extends EditRecord
{
    protected static string $resource = LetterOfAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
