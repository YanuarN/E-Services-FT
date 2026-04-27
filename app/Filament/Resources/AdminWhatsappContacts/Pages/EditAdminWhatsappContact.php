<?php

namespace App\Filament\Resources\AdminWhatsappContacts\Pages;

use App\Filament\Resources\AdminWhatsappContacts\AdminWhatsappContactResource;
use Filament\Resources\Pages\EditRecord;

class EditAdminWhatsappContact extends EditRecord
{
    protected static string $resource = AdminWhatsappContactResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
