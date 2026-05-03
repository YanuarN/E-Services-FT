<?php

namespace App\Filament\Resources\ExamPermissionLetters\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\ExamPermissionLetters\ExamPermissionLetterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExamPermissionLetter extends ViewRecord
{
    protected static string $resource = ExamPermissionLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
