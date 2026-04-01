<?php

namespace App\Filament\Resources\ExamPermissionLetters\Pages;

use App\Filament\Resources\ExamPermissionLetters\ExamPermissionLetterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExamPermissionLetter extends EditRecord
{
    protected static string $resource = ExamPermissionLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
