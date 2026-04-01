<?php

namespace App\Filament\Resources\InternshipRecommendationLetters\Pages;

use App\Filament\Resources\InternshipRecommendationLetters\InternshipRecommendationLetterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInternshipRecommendationLetter extends EditRecord
{
    protected static string $resource = InternshipRecommendationLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
