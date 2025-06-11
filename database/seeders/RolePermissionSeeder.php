<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all resources and their permissions
        $resources = [
            // User Management
            'user' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'role' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Customer Management
            'pelanggan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'supplier' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Sales & Transactions
            'transaksi_penjualan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'delivery_order' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Operational
            'kendaraan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'pengiriman_driver' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'uang_jalan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Finance & Accounting
            'akun' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'faktur_pajak' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'expense_request' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'tbbm' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Master Data
            'item' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'province' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'regency' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'district' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'subdistrict' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Documents
            'surat' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
        ];

        // Create all permissions
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web'
                ]);
            }
        }

        // Create roles with specific permissions
        $this->createRoles($resources);

        $this->command->info('Roles and permissions created successfully!');
    }

    private function createRoles(array $resources): void
    {
        // 1. Super Admin - Full access to everything
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
            'deskripsi' => 'Complete system access with all permissions'
        ]);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin - Most permissions except force delete operations
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
            'deskripsi' => 'Near-complete access but cannot permanently delete records'
        ]);
        $adminPermissions = Permission::where('name', 'not like', '%force_delete%')->get();
        $admin->syncPermissions($adminPermissions);

        // 3. Sales - Customer, sales, and delivery-related permissions
        $sales = Role::firstOrCreate([
            'name' => 'sales',
            'guard_name' => 'web',
            'deskripsi' => 'Customer, sales, and delivery-related permissions only'
        ]);
        $salesResources = ['pelanggan', 'supplier', 'transaksi_penjualan', 'delivery_order', 'item', 'province', 'regency', 'district', 'subdistrict'];
        $this->assignResourcePermissions($sales, $salesResources, ['view', 'view_any', 'create', 'update', 'delete']);

        // 4. Operational - Delivery, driver, vehicle, and operational permissions
        $operational = Role::firstOrCreate([
            'name' => 'operational',
            'guard_name' => 'web',
            'deskripsi' => 'Delivery, driver, vehicle, and operational permissions only'
        ]);
        $operationalResources = ['delivery_order', 'pengiriman_driver', 'kendaraan', 'uang_jalan', 'item', 'province', 'regency', 'district', 'subdistrict'];
        $this->assignResourcePermissions($operational, $operationalResources, ['view', 'view_any', 'create', 'update', 'delete']);

        // 5. Driver - Limited view and update permissions for deliveries only
        $driver = Role::firstOrCreate([
            'name' => 'driver',
            'guard_name' => 'web',
            'deskripsi' => 'Limited view and update permissions for deliveries only'
        ]);
        $driverResources = ['delivery_order', 'pengiriman_driver', 'uang_jalan'];
        $this->assignResourcePermissions($driver, $driverResources, ['view', 'view_any', 'update']);

        // 6. Finance - Accounting, transactions, and financial permissions
        $finance = Role::firstOrCreate([
            'name' => 'finance',
            'guard_name' => 'web',
            'deskripsi' => 'Accounting, transactions, and financial permissions only'
        ]);
        $financeResources = ['akun', 'faktur_pajak', 'expense_request', 'tbbm', 'transaksi_penjualan', 'delivery_order'];
        $this->assignResourcePermissions($finance, $financeResources, ['view', 'view_any', 'create', 'update', 'delete']);

        // 7. Administration - User management and document permissions
        $administration = Role::firstOrCreate([
            'name' => 'administration',
            'guard_name' => 'web',
            'deskripsi' => 'User management and document permissions only'
        ]);
        $administrationResources = ['user', 'role', 'surat'];
        $this->assignResourcePermissions($administration, $administrationResources, ['view', 'view_any', 'create', 'update', 'delete']);
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

        $role->syncPermissions($permissions);
    }
}
