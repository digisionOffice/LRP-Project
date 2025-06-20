<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AttendancePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding attendance permissions...');

        // Create attendance-related permissions
        $attendanceResources = [
            'absensi' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'shift' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'schedule' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
        ];

        foreach ($attendanceResources as $resource => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$action}_{$resource}";
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
                $this->command->info("Created permission: {$permissionName}");
            }
        }

        // Update existing roles with attendance permissions
        $this->updateRolePermissions();

        $this->command->info('Attendance permissions seeded successfully!');
    }

    private function updateRolePermissions(): void
    {
        $this->command->info('Updating role permissions...');

        // Super Admin - gets all permissions automatically
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
            $this->command->info('Updated super_admin permissions');
        }

        // Admin - gets all permissions except force_delete
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $adminPermissions = Permission::where('name', 'not like', '%force_delete%')->get();
            $admin->syncPermissions($adminPermissions);
            $this->command->info('Updated admin permissions');
        }

        // Administration - gets attendance management permissions
        $administration = Role::where('name', 'administration')->first();
        if ($administration) {
            $administrationResources = ['user', 'role', 'absensi', 'shift', 'schedule'];
            $this->assignResourcePermissions($administration, $administrationResources, ['view', 'view_any', 'create', 'update', 'delete']);
            $this->command->info('Updated administration permissions');
        }

        // Karyawan - gets limited attendance permissions
        $karyawan = Role::firstOrCreate([
            'name' => 'karyawan',
            'guard_name' => 'web',
            'deskripsi' => 'Employee role with limited attendance permissions'
        ]);

        $karyawanResources = ['absensi'];
        $this->assignResourcePermissions($karyawan, $karyawanResources, ['view', 'view_any', 'create', 'update']);
        
        // Add view-only permissions for schedule and shift
        $karyawanViewOnlyResources = ['schedule', 'shift'];
        $this->assignResourcePermissions($karyawan, $karyawanViewOnlyResources, ['view', 'view_any']);
        
        $this->command->info('Updated karyawan permissions');
    }

    private function assignResourcePermissions(Role $role, array $resources, array $actions): void
    {
        $permissions = [];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissionName = "{$action}_{$resource}";
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $permissions[] = $permission;
                }
            }
        }

        // Get existing permissions and merge with new ones
        $existingPermissions = $role->permissions;
        $allPermissions = $existingPermissions->merge($permissions)->unique('id');
        
        $role->syncPermissions($allPermissions);
    }
}
