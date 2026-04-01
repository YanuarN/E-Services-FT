<?php

namespace App\Filament\Resources\TestingPermissionRequestLetters\Pages;

use App\Filament\Resources\TestingPermissionRequestLetters\TestingPermissionRequestLetterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTestingPermissionRequestLetter extends EditRecord
{
    protected static string $resource = TestingPermissionRequestLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
