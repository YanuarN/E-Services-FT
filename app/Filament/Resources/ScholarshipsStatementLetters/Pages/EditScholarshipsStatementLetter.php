<?php

namespace App\Filament\Resources\ScholarshipsStatementLetters\Pages;

use App\Filament\Resources\ScholarshipsStatementLetters\ScholarshipsStatementLetterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditScholarshipsStatementLetter extends EditRecord
{
    protected static string $resource = ScholarshipsStatementLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
