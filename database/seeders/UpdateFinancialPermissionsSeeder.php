<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateFinancialPermissionsSeeder extends Seeder
{
    /**
     * Run the seeder to update financial permissions for existing roles.
     */
    public function run(): void
    {
        $this->command->info('Updating financial permissions for existing roles...');

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure financial permissions exist
        $financialResources = ['invoice', 'receipt', 'tax_invoice'];
        $actions = ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'];

        foreach ($financialResources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web'
                ]);
            }
        }

        // Update Finance role - Full access to financial models
        $finance = Role::where('name', 'finance')->first();
        if ($finance) {
            $financeResources = ['akun', 'faktur_pajak', 'invoice', 'receipt', 'tax_invoice', 'expense_request', 'tbbm', 'transaksi_penjualan', 'delivery_order'];
            $this->assignResourcePermissions($finance, $financeResources, ['view', 'view_any', 'create', 'update', 'delete']);
            $this->command->info('Updated Finance role permissions');
        }

        // Update Sales role - Can view and manage invoices and receipts
        $sales = Role::where('name', 'sales')->first();
        if ($sales) {
            $salesResources = ['pelanggan', 'supplier', 'transaksi_penjualan', 'delivery_order', 'invoice', 'receipt', 'item', 'province'];
            $this->assignResourcePermissions($sales, $salesResources, ['view', 'view_any', 'create', 'update', 'delete']);
            $this->command->info('Updated Sales role permissions');
        }

        // Update Operational role - View-only access to financial documents
        $operational = Role::where('name', 'operational')->first();
        if ($operational) {
            // Keep existing operational permissions
            $operationalResources = ['delivery_order', 'pengiriman_driver', 'kendaraan', 'uang_jalan', 'item', 'province'];
            $this->assignResourcePermissions($operational, $operationalResources, ['view', 'view_any', 'create', 'update', 'delete']);

            // Add view-only permissions for financial documents
            $operationalFinancialResources = ['invoice', 'receipt', 'tax_invoice'];
            $this->assignResourcePermissions($operational, $operationalFinancialResources, ['view', 'view_any']);
            $this->command->info('Updated Operational role permissions');
        }

        // Update Admin role - Should have access to everything except force delete
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $adminPermissions = Permission::where('name', 'not like', '%force_delete%')->get();
            $admin->syncPermissions($adminPermissions);
            $this->command->info('Updated Admin role permissions');
        }

        // Update Super Admin role - Should have access to everything
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
            $this->command->info('Updated Super Admin role permissions');
        }

        $this->command->info('Financial permissions updated successfully!');
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
        $existingPermissions = $role->permissions->pluck('id')->toArray();
        $newPermissionIds = collect($permissions)->pluck('id')->toArray();
        $allPermissionIds = array_unique(array_merge($existingPermissions, $newPermissionIds));

        $allPermissions = Permission::whereIn('id', $allPermissionIds)->get();
        $role->syncPermissions($allPermissions);
    }
}
