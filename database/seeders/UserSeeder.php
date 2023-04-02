<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Permission::create(['name' => 'list-categories']);
        Permission::create(['name' => 'view-categories']);
        Permission::create(['name' => 'create-categories']);
        Permission::create(['name' => 'edit-categories']);
        Permission::create(['name' => 'delete-categories']);

        Permission::create(['name' => 'list-products']);
        Permission::create(['name' => 'view-products']);
        Permission::create(['name' => 'create-products']);
        Permission::create(['name' => 'edit-products']);
        Permission::create(['name' => 'delete-products']);

        $adminRole = Role::create(['name' => 'admin']);
        $editorRole = Role::create(['name' => 'editor']);
        $viewerRole = Role::create(['name' => 'viewer']);
        $memberRole = Role::create(['name' => 'member']);

        $adminRole->givePermissionTo([
            'list-categories',
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',
            'list-products',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
        ]);

        $editorRole->givePermissionTo([
            'list-categories',
            'view-categories',
            'edit-categories',
            'list-products',
            'view-products',
            'edit-products',
        ]);

        $viewerRole->givePermissionTo([
            'list-products',
            'view-products',
        ]);

        $memberRole->givePermissionTo([
            'list-products',
            'view-products',
        ]);

        // create user
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('12345678')
        ]);

        $admin->assignRole($adminRole);
        $admin->givePermissionTo([
            'list-categories',
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',
            'list-products',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
        ]);

        $editor = User::create([
            'name' => 'editor',
            'email' => 'editor@example.com',
            'password' => bcrypt('12345678'),
        ]);

        $editor->assignRole($editorRole);
        $editor->givePermissionTo([
            'list-categories',
            'view-categories',
            'edit-categories',
            'list-products',
            'view-products',
            'edit-products',
        ]);

        $viewer = User::create([
            'name' => 'viewer',
            'email' => 'viewer@example.com',
            'password' => bcrypt('12345678')
        ]);

        $viewer->assignRole($viewerRole);
        $viewer->givePermissionTo([
            'list-products',
            'view-products',
        ]);


    }
}
