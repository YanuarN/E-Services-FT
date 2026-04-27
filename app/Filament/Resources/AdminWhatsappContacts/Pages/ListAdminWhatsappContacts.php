<?php

namespace App\Filament\Resources\AdminWhatsappContacts\Pages;

use App\Filament\Resources\AdminWhatsappContacts\AdminWhatsappContactResource;
use Filament\Resources\Pages\ListRecords;

class ListAdminWhatsappContacts extends ListRecords
{
    protected static string $resource = AdminWhatsappContactResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
