<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SankalpPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'view_sankalps',
            'show_sankalp',
            'edit_sankalp',
        ] as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
