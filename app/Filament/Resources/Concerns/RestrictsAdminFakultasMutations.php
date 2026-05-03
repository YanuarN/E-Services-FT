<?php

namespace App\Filament\Resources\Concerns;

use App\Filament\Support\AdminAccess;

trait RestrictsAdminFakultasMutations
{
    public static function canCreate(): bool
    {
        return AdminAccess::canMutate();
    }

    public static function canEdit($record): bool
    {
        return AdminAccess::canMutate();
    }

    public static function canDelete($record): bool
    {
        return AdminAccess::canMutate();
    }

    public static function canDeleteAny(): bool
    {
        return AdminAccess::canMutate();
    }
}
