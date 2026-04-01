<?php

namespace App\Filament\Resources\InternshipRecommendationLetters\Pages;

use App\Filament\Resources\InternshipRecommendationLetters\InternshipRecommendationLetterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInternshipRecommendationLetter extends ViewRecord
{
    protected static string $resource = InternshipRecommendationLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
