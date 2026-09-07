<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

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
                ->where('slug', '!=', fn ($p) => str($p)->endsWith('.delete'))
                ->pluck('id')
        );

        // Ikaw bilang unang Super Admin — palitan kung iba yung email mo
        $owner = User::where('email', 'jonrhem10@gmail.com')->first();

        if ($owner) {
            $owner->update(['status' => 'approved']);
            $owner->roles()->syncWithoutDetaching([$superAdmin->id]);
        }
    }
}
