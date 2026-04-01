<?php

namespace App\Filament\Resources\ResearchPermissionLetters\Pages;

use App\Filament\Resources\ResearchPermissionLetters\ResearchPermissionLetterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditResearchPermissionLetter extends EditRecord
{
    protected static string $resource = ResearchPermissionLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
