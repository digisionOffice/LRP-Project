<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\Entitas;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing jabatan and divisi IDs
        $direkturJabatan = Jabatan::where('nama', 'Direktur')->first();
        $managerJabatan = Jabatan::where('nama', 'Manager')->first();
        $supervisorJabatan = Jabatan::where('nama', 'Supervisor')->first();
        $staffJabatan = Jabatan::where('nama', 'Staff')->first();
        $driverJabatan = Jabatan::where('nama', 'Driver')->first();
        $adminJabatan = Jabatan::where('nama', 'Admin')->first();

        $direksiDivisi = Divisi::where('nama', 'Direksi')->first();
        $salesDivisi = Divisi::where('nama', 'Sales')->first();
        $operasionalDivisi = Divisi::where('nama', 'Operasional')->first();
        $administrasiDivisi = Divisi::where('nama', 'Administrasi')->first();
        $keuanganDivisi = Divisi::where('nama', 'Keuangan')->first();
        $hrdDivisi = Divisi::where('nama', 'HRD')->first();
        $itDivisi = Divisi::where('nama', 'IT')->first();

        // Create Super Admin User
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@lrp.com'],
            [
                'name' => 'Super Administrator',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
                'no_induk' => 'SYS001',
                'hp' => '081234567890',
                'id_jabatan' => $direkturJabatan?->id,
                'id_divisi' => $direksiDivisi?->id,
                'created_by' => null, // Super admin created by system
            ]
        );

        // Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@lrp.com'],
            [
                'name' => 'Administrator',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
                'no_induk' => 'SYS002',
                'hp' => '081234567891',
                'id_jabatan' => $managerJabatan?->id,
                'id_divisi' => $administrasiDivisi?->id,
                'created_by' => $superAdmin->id,
            ]
        );

        // Create Sales User
        $sales = User::updateOrCreate(
            ['email' => 'sales@lrp.com'],
            [
                'name' => 'Sales Manager',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
                'no_induk' => 'SYS003',
                'hp' => '081234567892',
                'id_jabatan' => $managerJabatan?->id,
                'id_divisi' => $salesDivisi?->id,
                'created_by' => $superAdmin->id,
            ]
        );

        // Create Operational User
        $operational = User::updateOrCreate(
            ['email' => 'operational@lrp.com'],
            [
                'name' => 'Operational Manager',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
                'no_induk' => 'SYS004',
                'hp' => '081234567893',
                'id_jabatan' => $managerJabatan?->id,
                'id_divisi' => $operasionalDivisi?->id,
                'created_by' => $superAdmin->id,
            ]
        );

        // Create Driver User
        $driver = User::updateOrCreate(
            ['email' => 'driver@lrp.com'],
            [
                'name' => 'Driver Test',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
                'no_induk' => 'SYS005',
                'hp' => '081234567894',
                'id_jabatan' => $driverJabatan?->id,
                'id_divisi' => $operasionalDivisi?->id,
                'created_by' => $superAdmin->id,
            ]
        );

        // Create Finance User
        $finance = User::updateOrCreate(
            ['email' => 'finance@lrp.com'],
            [
                'name' => 'Finance Manager',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
                'no_induk' => 'SYS006',
                'hp' => '081234567895',
                'id_jabatan' => $managerJabatan?->id,
                'id_divisi' => $keuanganDivisi?->id,
                'created_by' => $superAdmin->id,
            ]
        );

        // Create Administration User
        $administration = User::updateOrCreate(
            ['email' => 'admin.staff@lrp.com'],
            [
                'name' => 'Administration Staff',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
                'no_induk' => 'SYS007',
                'hp' => '081234567896',
                'id_jabatan' => $staffJabatan?->id,
                'id_divisi' => $administrasiDivisi?->id,
                'created_by' => $superAdmin->id,
            ]
        );

        // Assign Filament Shield roles to users
        // Get roles from database
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $salesRole = Role::where('name', 'sales')->first();
        $operationalRole = Role::where('name', 'operational')->first();
        $driverRole = Role::where('name', 'driver')->first();
        $financeRole = Role::where('name', 'finance')->first();
        $administrationRole = Role::where('name', 'administration')->first();

        // Assign Filament Shield roles to users
        if ($superAdminRole) {
            $superAdmin->assignRole($superAdminRole);
        }

        if ($adminRole) {
            $admin->assignRole($adminRole);
        }

        if ($salesRole) {
            $sales->assignRole($salesRole);
        }

        if ($operationalRole) {
            $operational->assignRole($operationalRole);
        }

        if ($driverRole) {
            $driver->assignRole($driverRole);
        }

        if ($financeRole) {
            $finance->assignRole($financeRole);
        }

        if ($administrationRole) {
            $administration->assignRole($administrationRole);
        }

        $this->command->info('Users created and roles assigned successfully!');
    }
}
