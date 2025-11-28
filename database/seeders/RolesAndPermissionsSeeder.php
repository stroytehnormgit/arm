<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $plannedListPermissions = [
            'planned-list.view',
            'planned-list.create',
            'planned-list.edit',
            'planned-list.delete',
            'planned-list.export',
        ];

        $activeListPermissions = [
            'active-list.view',
            'active-list.create',
            'active-list.edit',
            'active-list.delete',
        ];

        $reportsPermissions = [
            'reports.view',
            'reports.create',
            'reports.edit',
            'reports.delete',
        ];

        $archivePermissions = [
            'archive.view',
            'archive.create',
            'archive.edit',
            'archive.delete',
        ];

        $filesPermissions = [
            'files.view',
            'files.upload',
            'files.delete',
        ];

        $adminPermissions = [
            'users.manage',
            'stages.manage',
            'all-blocks.access',
        ];

        $allPermissions = array_merge(
            $plannedListPermissions,
            $activeListPermissions,
            $reportsPermissions,
            $archivePermissions,
            $filesPermissions,
            $adminPermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo($allPermissions);

        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        
        $employeeRole->givePermissionTo([
            'planned-list.view',
            'planned-list.create',
            'planned-list.edit',
            'planned-list.delete',
            'planned-list.export',
            'active-list.view',
            'active-list.create',
            'active-list.edit',
            'active-list.delete',
            'reports.view',
            'reports.create',
            'reports.edit',
            'reports.delete',
            'archive.view',
            'archive.create',
            'archive.edit',
            'archive.delete',
            'files.view',
            'files.upload',
            'files.delete',
        ]);

        $admin = User::where('email', 'admin@arm.local')->first();
        if ($admin) {
            $admin->assignRole('admin');
            $admin->block_rank = null;
            $admin->save();
        }

        $employee = User::where('email', 'user@arm.local')->first();
        if ($employee) {
            $employee->assignRole('employee');
            $employee->block_rank = 1;
            $employee->save();
        }
    }
}

