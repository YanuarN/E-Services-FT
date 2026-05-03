<?php

namespace App\Filament\Resources\TestingPermissionRequestLetters\Pages;

use App\Filament\Support\AdminAccess;
use App\Filament\Resources\TestingPermissionRequestLetters\TestingPermissionRequestLetterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTestingPermissionRequestLetter extends ViewRecord
{
    protected static string $resource = TestingPermissionRequestLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }
}
