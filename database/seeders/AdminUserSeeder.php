<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        $superAdminRole = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => $guard,
        ]);

        $adminFakultasRole = Role::firstOrCreate([
            'name' => 'adminFakultas',
            'guard_name' => $guard,
        ]);

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@eservices.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $adminFakultas = User::updateOrCreate(
            ['email' => 'adminfakultas@eservices.test'],
            [
                'name' => 'Admin Fakultas',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $superAdmin->syncRoles([$superAdminRole]);
        $adminFakultas->syncRoles([$adminFakultasRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
