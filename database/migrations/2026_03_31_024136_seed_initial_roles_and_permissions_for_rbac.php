<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $permissions = [
            'dashboard.read',
            'akun-admin-fakultas.read',
            'akun-admin-fakultas.create',
            'akun-admin-fakultas.update',
            'akun-admin-fakultas.delete',
            'layanan-surat.read',
            'layanan-surat.create',
            'layanan-surat.update',
            'layanan-surat.delete',
            'ruangan.read',
            'ruangan.create',
            'ruangan.update',
            'ruangan.delete',
            'riwayat-ajuan.read',
            'riwayat-ajuan.export',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => $guard,
        ]);

        $adminFakultas = Role::firstOrCreate([
            'name' => 'adminFakultas',
            'guard_name' => $guard,
        ]);

        $readPermissions = array_values(array_filter(
            $permissions,
            static fn (string $permissionName): bool => str_contains($permissionName, '.read')
        ));

        $superAdmin->syncPermissions($permissions);
        $adminFakultas->syncPermissions($readPermissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $permissions = [
            'dashboard.read',
            'akun-admin-fakultas.read',
            'akun-admin-fakultas.create',
            'akun-admin-fakultas.update',
            'akun-admin-fakultas.delete',
            'layanan-surat.read',
            'layanan-surat.create',
            'layanan-surat.update',
            'layanan-surat.delete',
            'ruangan.read',
            'ruangan.create',
            'ruangan.update',
            'ruangan.delete',
            'riwayat-ajuan.read',
            'riwayat-ajuan.export',
        ];

        Role::query()->whereIn('name', ['SuperAdmin', 'adminFakultas'])->where('guard_name', $guard)->delete();
        Permission::query()->whereIn('name', $permissions)->where('guard_name', $guard)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
