<?php

namespace App\Filament\Support;

use App\Models\User;

class AdminAccess
{
    public static function canMutate(): bool
    {
        return ! self::isReadOnlyAdminFakultas();
    }

    public static function isReadOnlyAdminFakultas(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasRole('adminFakultas')
            && ! $user->hasRole('SuperAdmin');
    }
}
