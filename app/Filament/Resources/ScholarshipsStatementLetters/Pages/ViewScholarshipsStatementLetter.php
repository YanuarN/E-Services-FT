<?php

namespace App\Filament\Resources\ScholarshipsStatementLetters\Pages;

use App\Filament\Resources\ScholarshipsStatementLetters\ScholarshipsStatementLetterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewScholarshipsStatementLetter extends ViewRecord
{
    protected static string $resource = ScholarshipsStatementLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
