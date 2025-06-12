<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'employee',
            'admin',
        ];

        $permissions = [
            'campaigns.create', 'campaigns.edit', 'campaigns.delete', 'campaigns.view',
            'categories.create', 'categories.edit', 'categories.delete', 'categories.view',
            'donations.create', 'donations.view', 'donations.view_all', 'donations.manage', 'donations.refund',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'activity_logs.view',
            'admin.dashboard', 'admin.settings',
            'reports.view', 'reports.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        $employeePermissions = [
            'campaigns.create', 'campaigns.edit', 'campaigns.delete', 'campaigns.view',
            'categories.view',
            'donations.create', 'donations.view',
            'users.view',
        ];

        $adminPermissions = array_merge($employeePermissions, [
            'donations.view_all', 'donations.manage', 'donations.refund',
            'categories.create', 'categories.edit', 'categories.delete',
            'users.create', 'users.edit', 'users.delete',
            'activity_logs.view',
            'admin.dashboard', 'admin.settings',
            'reports.view', 'reports.export',
        ]);

        $employeeRole = Role::where('name', 'employee')->first();
        $adminRole = Role::where('name', 'admin')->first();

        if ($employeeRole) {
            $employeeRole->syncPermissions($employeePermissions);
        }
        if ($adminRole) {
            $adminRole->syncPermissions($adminPermissions);
        }
    }
}
