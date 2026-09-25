<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = ['clients', 'lots', 'payments', 'projects', 'reservations', 'agents', 'documents', 'reports', 'users', 'roles', 'settings'];
        $actions = ['view', 'create', 'edit', 'delete'];

        $permissions = collect();

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions->push(Permission::create([
                    'name' => ucfirst($action).' '.ucfirst($module),
                    'slug' => "{$module}.{$action}",
                    'group' => $module,
                ]));
            }
        }

        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Buong access sa lahat ng modules, roles, at user management.',
        ]);
        $superAdmin->permissions()->attach($permissions->pluck('id'));

        $admin = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Access sa operations, walang access sa roles management.',
        ]);
        $admin->permissions()->attach(
            $permissions->reject(fn ($p) => $p->group === 'roles')->pluck('id')
        );

        $staff = Role::create([
            'name' => 'Staff',
            'slug' => 'staff',
            'description' => 'View at limited edit access lang.',
        ]);
        $staff->permissions()->attach(
            $permissions->whereIn('group', ['clients', 'lots', 'payments'])
                ->reject(fn ($p) => str($p->slug)->endsWith('.delete'))
                ->pluck('id')
        );

        $owner = User::updateOrCreate(
            ['email' => 'jonrhem10@gmail.com'],
            [
                'name' => 'Dann Johnrem Araullo',
                'password' => Hash::make('123123123'),
                'status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

        $owner->roles()->syncWithoutDetaching([$superAdmin->id]);
    }
}
