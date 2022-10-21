<?php

namespace Monet\Framework\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $schema = $this->schema();

        $permissions = $this->getPermissions($schema);
        foreach ($permissions as $permission) {
            try {
                Permission::create(['name' => $permission]);
            } catch (PermissionAlreadyExists) {
                // Role may already exist if we're installing into an old database
            }
        }

        foreach ($schema as $role) {
            try {
                Role::create(collect($role)->except(['permissions'])->all())
                    ->givePermissionTo($role['permissions']);
            } catch (RoleAlreadyExists) {
                Role::findByName('Administrator')
                    ->givePermissionTo($role['permissions']);
            }
        }
    }

    protected function schema(): array
    {
        return [
            [
                'name' => 'User',
                'guard_name' => 'web',
                'permissions' => [],
            ],
            [
                'name' => 'Administrator',
                'guard_name' => 'web',
                'permissions' => [
                    'view admin',

                    'create users',
                    'update users',
                    'delete users',

                    'update modules',
                    'delete modules',

                    'update themes',
                    'delete themes'
                ],
            ],
        ];
    }

    protected function getPermissions(array $schema): array
    {
        $permissions = [];

        foreach ($schema as $role) {
            foreach ($role['permissions'] as $permission) {
                $permissions[] = $permission;
            }
        }

        return array_unique($permissions);
    }
}
