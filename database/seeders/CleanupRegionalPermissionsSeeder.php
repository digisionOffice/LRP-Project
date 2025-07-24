<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CleanupRegionalPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning up regional permissions...');

        // List of permissions to remove (regency, district, subdistrict)
        $permissionsToRemove = [
            // Regency permissions
            'view_regency',
            'view_any_regency',
            'create_regency',
            'update_regency',
            'delete_regency',
            'delete_any_regency',
            'force_delete_regency',
            'force_delete_any_regency',
            'restore_regency',
            'restore_any_regency',

            // District permissions
            'view_district',
            'view_any_district',
            'create_district',
            'update_district',
            'delete_district',
            'delete_any_district',
            'force_delete_district',
            'force_delete_any_district',
            'restore_district',
            'restore_any_district',

            // Subdistrict permissions
            'view_subdistrict',
            'view_any_subdistrict',
            'create_subdistrict',
            'update_subdistrict',
            'delete_subdistrict',
            'delete_any_subdistrict',
            'force_delete_subdistrict',
            'force_delete_any_subdistrict',
            'restore_subdistrict',
            'restore_any_subdistrict',
        ];

        // Remove permissions from all roles first
        foreach ($permissionsToRemove as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                // Remove from all roles
                $roles = Role::whereHas('permissions', function ($query) use ($permission) {
                    $query->where('permissions.id', $permission->id);
                })->get();

                foreach ($roles as $role) {
                    $role->revokePermissionTo($permission);
                    $this->command->info("Removed permission '{$permissionName}' from role '{$role->name}'");
                }

                // Delete the permission
                $permission->delete();
                $this->command->info("Deleted permission: {$permissionName}");
            }
        }

        $this->command->info('Regional permissions cleanup completed.');
        $this->command->info('Note: Regency, District, and Subdistrict are now managed through Province Resource relation managers.');
    }
}
