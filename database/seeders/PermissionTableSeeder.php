<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = [
            'ship-list',
            'ship-create',
            'ship-edit',
            'ship-delete',
            'ship-verif',
            'user-verif',
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
        ];

        $roles = [
            'admin',
            'user',
        ];

        foreach ($permission as $key => $value) {
            Permission::create(['name' => $value]);
        }

        foreach ($roles as $key => $value) {
            if ($value == 'admin') {
                $role = Role::create(['name' => $value]);
                $role->givePermissionTo([
                    'ship-list',
                    'user-edit',
                    'user-list',
                    'user-verif',
                    'user-delete',
                    'ship-delete',
                    'ship-verif',
                    'ship-edit',
                ]);
            } else {
                $role = Role::create(['name' => $value]);
                $role->givePermissionTo([
                    'ship-list',
                    'ship-create',
                    'ship-edit',
                    'user-edit',
                ]);
            }
        }
    }
}
