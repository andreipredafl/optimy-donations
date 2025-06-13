<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = [
            'campaigns.create', 'campaigns.edit', 'campaigns.delete', 'campaigns.view',
            'categories.create', 'categories.edit', 'categories.delete', 'categories.view',
            'donations.create', 'donations.view', 'donations.view_all', 'donations.manage', 'donations.refund',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'activity_logs.view',
            'admin.dashboard', 'admin.settings',
            'reports.view', 'reports.export',
        ];

        $employeePermissions = [
            'campaigns.create', 'campaigns.edit', 'campaigns.delete', 'campaigns.view',
            'categories.view',
            'donations.create', 'donations.view',
        ];

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $employeeRole = Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions($allPermissions);
        $employeeRole->syncPermissions($employeePermissions);
    }
}
