<?php

namespace App\Filament\Resources\ResearchDataRequestLetters\Pages;

use App\Filament\Resources\ResearchDataRequestLetters\ResearchDataRequestLetterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditResearchDataRequestLetter extends EditRecord
{
    protected static string $resource = ResearchDataRequestLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
