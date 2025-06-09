<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\Entitas;
use Illuminate\Support\Facades\Hash;

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
                'role' => 'super_admin',
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
                'role' => 'admin',
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
                'role' => 'sales',
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
                'role' => 'operational',
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
                'role' => 'driver',
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
                'role' => 'finance',
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
                'role' => 'administration',
                'is_active' => true,
                'no_induk' => 'SYS007',
                'hp' => '081234567896',
                'id_jabatan' => $staffJabatan?->id,
                'id_divisi' => $administrasiDivisi?->id,
                'created_by' => $superAdmin->id,
            ]
        );

        // Assign roles to users using UserRole model
        // Get roles from database
        $superAdminRole = Role::where('nama', 'Super Admin')->first();
        $adminRole = Role::where('nama', 'Admin')->first();
        $salesRole = Role::where('nama', 'Sales')->first();
        $operationalRole = Role::where('nama', 'Operasional')->first();
        $driverRole = Role::where('nama', 'Driver')->first();
        $financeRole = Role::where('nama', 'Keuangan')->first();
        $administrationRole = Role::where('nama', 'Administrasi')->first();

        // Create UserRole relationships
        if ($superAdminRole) {
            UserRole::firstOrCreate(
                [
                    'id_user' => $superAdmin->id,
                    'id_role' => $superAdminRole->id,
                ],
                [
                    'created_by' => $superAdmin->id,
                ]
            );
        }

        if ($adminRole) {
            UserRole::firstOrCreate(
                [
                    'id_user' => $admin->id,
                    'id_role' => $adminRole->id,
                ],
                [
                    'created_by' => $superAdmin->id,
                ]
            );
        }

        if ($salesRole) {
            UserRole::firstOrCreate(
                [
                    'id_user' => $sales->id,
                    'id_role' => $salesRole->id,
                ],
                [
                    'created_by' => $superAdmin->id,
                ]
            );
        }

        if ($operationalRole) {
            UserRole::firstOrCreate(
                [
                    'id_user' => $operational->id,
                    'id_role' => $operationalRole->id,
                ],
                [
                    'created_by' => $superAdmin->id,
                ]
            );
        }

        if ($driverRole) {
            UserRole::firstOrCreate(
                [
                    'id_user' => $driver->id,
                    'id_role' => $driverRole->id,
                ],
                [
                    'created_by' => $superAdmin->id,
                ]
            );
        }

        if ($financeRole) {
            UserRole::firstOrCreate(
                [
                    'id_user' => $finance->id,
                    'id_role' => $financeRole->id,
                ],
                [
                    'created_by' => $superAdmin->id,
                ]
            );
        }

        if ($administrationRole) {
            UserRole::firstOrCreate(
                [
                    'id_user' => $administration->id,
                    'id_role' => $administrationRole->id,
                ],
                [
                    'created_by' => $superAdmin->id,
                ]
            );
        }

        // Create additional test users using factory
        User::factory(10)->create([
            'role' => 'user',
            'is_active' => true,
        ]);
    }
}
