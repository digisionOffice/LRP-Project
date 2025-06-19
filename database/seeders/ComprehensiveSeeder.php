<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

// Master Data Models
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\EntitasTipe;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\ItemKategori;
use App\Models\SatuanDasar;
use App\Models\Akun;

// User & Permission Models
use App\Models\User;
// use App\Models\Role;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Business Models
use App\Models\Item;
use App\Models\Tbbm;
use App\Models\Pelanggan;
use App\Models\AlamatPelanggan;
use App\Models\Supplier;
use App\Models\Kendaraan;
use App\Models\ExpenseRequest;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\PenjualanDetail;
use App\Models\PengirimanDriver;
use App\Models\UangJalan;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\TaxInvoice;

class ComprehensiveSeeder extends Seeder
{
    /**
     * Run the comprehensive database seeder.
     * This seeder consolidates all individual seeders into one centralized location.
     */
    public function run(): void
    {
        $this->command->info('Starting comprehensive database seeding...');

        // 1. Master Data (must be first)
        $this->seedMasterData();

        // 2. Roles and Permissions (must be before users)
        $this->seedRolesAndPermissions();

        // 3. Users (depends on roles and master data)
        $this->seedUsers();

        // 4. Items (depends on master data)
        $this->seedItems();

        // 5. TBBM (depends on master data)
        $this->seedTbbm();

        // 6. Customers (depends on master data)
        $this->seedPelanggan();

        // 7. Customer Addresses (depends on customers)
        $this->seedAlamatPelanggan();

        // 8. Suppliers (depends on master data)
        $this->seedSuppliers();

        // 9. Vehicles
        $this->seedKendaraan();

        // 10. Expense Requests (depends on users)
        $this->seedExpenseRequests();

        // 11. Sales Orders and Delivery Orders (depends on multiple entities)
        $this->seedSalesAndDeliveryOrders();

        // 12. Financial Management (depends on sales orders and delivery orders)
        $this->seedFinancialManagement();

        // 13. Test Data for specific features
        // $this->seedTestData();

        $this->command->info('Comprehensive database seeding completed successfully!');
    }

    /**
     * ========================================
     * SECTION 1: MASTER DATA
     * ========================================
     */
    private function seedMasterData(): void
    {
        $this->command->info('Seeding master data...');

        $this->command->info('Seeding master data indonesian administrative data...');

        // Seed Indonesian administrative data
        $this->seedIndonesianAdministrativeData();

        $this->command->info('Seeding master data riau province data...');

        // Seed Riau Province detailed data
        $this->seedRiauProvinceData();

        $this->command->info('Seeding master data other master data...');

        // Seed other master data
        $this->seedOtherMasterData();
    }

    private function seedIndonesianAdministrativeData(): void
    {
        // This would contain the comprehensive Indonesian administrative data
        // For brevity, including key provinces and their administrative divisions

        $provinces = [
            ['id' => '11', 'name' => 'Aceh'],
            ['id' => '12', 'name' => 'Sumatera Utara'],
            ['id' => '13', 'name' => 'Sumatera Barat'],
            ['id' => '14', 'name' => 'Riau'],
            ['id' => '15', 'name' => 'Jambi'],
            ['id' => '16', 'name' => 'Sumatera Selatan'],
            ['id' => '17', 'name' => 'Bengkulu'],
            ['id' => '18', 'name' => 'Lampung'],
            ['id' => '19', 'name' => 'Kepulauan Bangka Belitung'],
            ['id' => '21', 'name' => 'Kepulauan Riau'],
            ['id' => '31', 'name' => 'DKI Jakarta'],
            ['id' => '32', 'name' => 'Jawa Barat'],
            ['id' => '33', 'name' => 'Jawa Tengah'],
            ['id' => '34', 'name' => 'DI Yogyakarta'],
            ['id' => '35', 'name' => 'Jawa Timur'],
            ['id' => '36', 'name' => 'Banten'],
        ];

        foreach ($provinces as $provinceData) {
            Province::Create($provinceData);
        }

        // Add basic regencies for Jakarta (commonly used)
        $jakartaRegencies = [
            ['id' => '3171', 'province_id' => '31', 'name' => 'Kepulauan Seribu'],
            ['id' => '3172', 'province_id' => '31', 'name' => 'Jakarta Utara'],
            ['id' => '3173', 'province_id' => '31', 'name' => 'Jakarta Barat'],
            ['id' => '3174', 'province_id' => '31', 'name' => 'Jakarta Pusat'],
            ['id' => '3175', 'province_id' => '31', 'name' => 'Jakarta Selatan'],
            ['id' => '3176', 'province_id' => '31', 'name' => 'Jakarta Timur'],
        ];

        foreach ($jakartaRegencies as $regencyData) {
            Regency::Create($regencyData);
        }

        // Add basic districts for Jakarta Pusat
        $jakartaPusatDistricts = [
            ['id' => '317401', 'regency_id' => '3174', 'name' => 'Gambir'],
            ['id' => '317402', 'regency_id' => '3174', 'name' => 'Sawah Besar'],
            ['id' => '317403', 'regency_id' => '3174', 'name' => 'Kemayoran'],
            ['id' => '317404', 'regency_id' => '3174', 'name' => 'Senen'],
            ['id' => '317405', 'regency_id' => '3174', 'name' => 'Cempaka Putih'],
            ['id' => '317406', 'regency_id' => '3174', 'name' => 'Menteng'],
            ['id' => '317407', 'regency_id' => '3174', 'name' => 'Tanah Abang'],
            ['id' => '317408', 'regency_id' => '3174', 'name' => 'Johar Baru'],
        ];

        foreach ($jakartaPusatDistricts as $districtData) {
            District::Create($districtData);
        }

        // Add basic subdistricts for Menteng
        $mentengSubdistricts = [
            ['id' => '3174060001', 'district_id' => '317406', 'name' => 'Menteng'],
            ['id' => '3174060002', 'district_id' => '317406', 'name' => 'Pegangsaan'],
            ['id' => '3174060003', 'district_id' => '317406', 'name' => 'Cikini'],
            ['id' => '3174060004', 'district_id' => '317406', 'name' => 'Gondangdia'],
        ];

        foreach ($mentengSubdistricts as $subdistrictData) {
            Subdistrict::Create($subdistrictData);
        }
    }

    private function seedRiauProvinceData(): void
    {

        // Get or create admin user for created_by field
        $adminUser = User::where('email', 'superadmin@lrp.com')->first()
            ?? User::where('name', 'super_admin')->first()
            ?? User::first();

        $riau = Province::where('id', '14')->first();

        if (!$riau) {
            $this->command->warn('Riau province not found. Skipping Riau province data.');
            return;
        }

        // Riau Regencies with their districts and subdistricts (abbreviated for space)
        $regenciesData = [
            [
                'id' => '1401',
                'name' => 'Kabupaten Kuantan Singingi',
                'districts' => [
                    ['id' => '140101', 'name' => 'Kuantan Mudik', 'subdistricts' => [
                        ['id' => '1401010001', 'name' => 'Muara Lembu'],
                        ['id' => '1401010002', 'name' => 'Sungai Jering'],
                        ['id' => '1401010003', 'name' => 'Kuantan Mudik'],
                    ]],
                    ['id' => '140102', 'name' => 'Kuantan Tengah', 'subdistricts' => [
                        ['id' => '1401020001', 'name' => 'Lubuk Jambi'],
                        ['id' => '1401020002', 'name' => 'Taluk Kuantan'],
                        ['id' => '1401020003', 'name' => 'Seberang Taluk'],
                    ]],
                ]
            ],
            [
                'id' => '1471',
                'name' => 'Kota Pekanbaru',
                'districts' => [
                    ['id' => '147101', 'name' => 'Sukajadi', 'subdistricts' => [
                        ['id' => '1471010001', 'name' => 'Kampung Melayu'],
                        ['id' => '1471010002', 'name' => 'Sukajadi'],
                        ['id' => '1471010003', 'name' => 'Kedung Kandang'],
                    ]],
                    ['id' => '147102', 'name' => 'Lima Puluh', 'subdistricts' => [
                        ['id' => '1471020001', 'name' => 'Rintis'],
                        ['id' => '1471020002', 'name' => 'Kebun Bunga'],
                        ['id' => '1471020003', 'name' => 'Tangkerang Labuai'],
                    ]],
                ]
            ],
            [
                'id' => '1473',
                'name' => 'Kota Dumai',
                'districts' => [
                    ['id' => '147301', 'name' => 'Dumai Barat', 'subdistricts' => [
                        ['id' => '1473010001', 'name' => 'Dumai Barat'],
                        ['id' => '1473010002', 'name' => 'Rimba Sekampung'],
                        ['id' => '1473010003', 'name' => 'Bukit Batrem'],
                    ]],
                ]
            ],
        ];

        // Create regencies, districts, and subdistricts
        foreach ($regenciesData as $regencyData) {
            $regency = Regency::Create([
                'id' => $regencyData['id'],
                'province_id' => $riau->id,
                'name' => $regencyData['name'],
                'created_by' => $adminUser?->id,
            ]);

            foreach ($regencyData['districts'] as $districtData) {
                $district = District::Create([
                    'id' => $districtData['id'],
                    'regency_id' => $regency->id,
                    'name' => $districtData['name'],
                    'created_by' => $adminUser?->id,
                ]);

                foreach ($districtData['subdistricts'] as $subdistrictData) {
                    Subdistrict::Create([
                        'id' => $subdistrictData['id'],
                        'district_id' => $district->id,
                        'name' => $subdistrictData['name'],
                        'created_by' => $adminUser?->id,
                    ]);
                }
            }
        }
    }

    private function seedOtherMasterData(): void
    {
        $this->command->info('Seeding entitas master data...');

        // Seed Entitas Tipe
        $entitasTipes = [
            ['nama' => 'Kantor Pusat'],
            ['nama' => 'Kantor Cabang'],
            ['nama' => 'Gudang'],
            ['nama' => 'TBBM'],
        ];

        foreach ($entitasTipes as $tipe) {
            EntitasTipe::Create($tipe);
        }

        $this->command->info('Seeding jabatan and divisi master data...');

        // Seed Jabatan
        $jabatans = [
            ['nama' => 'Direktur'],
            ['nama' => 'Manager'],
            ['nama' => 'Supervisor'],
            ['nama' => 'Staff'],
            ['nama' => 'Driver'],
            ['nama' => 'Admin'],
            ['nama' => 'Operator'],
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::Create($jabatan);
        }

        // Seed Divisi
        $divisis = [
            ['nama' => 'Direksi'],
            ['nama' => 'Sales'],
            ['nama' => 'Operasional'],
            ['nama' => 'Administrasi'],
            ['nama' => 'Keuangan'],
            ['nama' => 'HRD'],
            ['nama' => 'IT'],
        ];

        foreach ($divisis as $divisi) {
            Divisi::Create($divisi);
        }

        $this->command->info('Seeding item kategori master data...');

        // Seed Item Kategori
        $itemKategoris = [
            ['kode' => 'BBM', 'nama' => 'Bahan Bakar Minyak', 'deskripsi' => 'Kategori untuk produk BBM'],
            ['kode' => 'LPG', 'nama' => 'Liquefied Petroleum Gas', 'deskripsi' => 'Kategori untuk produk LPG'],
            ['kode' => 'LUBE', 'nama' => 'Pelumas', 'deskripsi' => 'Kategori untuk produk pelumas'],
            ['kode' => 'CHEM', 'nama' => 'Kimia', 'deskripsi' => 'Kategori untuk produk kimia'],
        ];

        foreach ($itemKategoris as $kategori) {
            ItemKategori::Create($kategori);
        }

        $this->command->info('Seeding satuan dasar master data...');

        // Seed Satuan Dasar
        $satuanDasars = [
            ['kode' => 'LTR', 'nama' => 'Liter', 'deskripsi' => 'Satuan Volume Liter'],
            ['kode' => 'KL', 'nama' => 'Kiloliter', 'deskripsi' => 'Satuan Volume Kiloliter'],
            ['kode' => 'KG', 'nama' => 'Kilogram', 'deskripsi' => 'Satuan Berat Kilogram'],
            ['kode' => 'TON', 'nama' => 'Ton', 'deskripsi' => 'Satuan Berat Ton'],
            ['kode' => 'PCS', 'nama' => 'Pieces', 'deskripsi' => 'Satuan Buah'],
        ];

        foreach ($satuanDasars as $satuan) {
            SatuanDasar::Create($satuan);
        }

        $this->command->info('Seeding akun master data...');


        // Seed Akun
        $akuns = [
            ['kode_akun' => '1000', 'nama_akun' => 'Kas', 'tipe_akun' => 'Aktiva', 'created_by' => 1],
            ['kode_akun' => '1100', 'nama_akun' => 'Bank', 'tipe_akun' => 'Aktiva', 'created_by' => 1],
            ['kode_akun' => '1200', 'nama_akun' => 'Piutang Dagang', 'tipe_akun' => 'Aktiva', 'created_by' => 1],
            ['kode_akun' => '1300', 'nama_akun' => 'Persediaan', 'tipe_akun' => 'Aktiva', 'created_by' => 1],
            ['kode_akun' => '2000', 'nama_akun' => 'Hutang Dagang', 'tipe_akun' => 'Kewajiban', 'created_by' => 1],
            ['kode_akun' => '3000', 'nama_akun' => 'Modal', 'tipe_akun' => 'Modal', 'created_by' => 1],
            ['kode_akun' => '4000', 'nama_akun' => 'Pendapatan', 'tipe_akun' => 'Pendapatan', 'created_by' => 1],
            ['kode_akun' => '5000', 'nama_akun' => 'Beban', 'tipe_akun' => 'Beban', 'created_by' => 1],
        ];

        foreach ($akuns as $akun) {
            Akun::Create($akun);
        }
    }

    /**
     * ========================================
     * SECTION 2: ROLES AND PERMISSIONS
     * ========================================
     */
    private function seedRolesAndPermissions(): void
    {
        $this->command->info('Seeding roles and permissions...');

        // Define resources and their actions
        $resources = [
            // User Management
            'user' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'role' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Master Data
            'province' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'regency' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'district' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'subdistrict' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'entitas_tipe' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'jabatan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'divisi' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'item_kategori' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'satuan_dasar' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'akun' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Business Entities
            'item' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'tbbm' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'pelanggan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'alamat_pelanggan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'supplier' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'kendaraan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Transactions
            'transaksi_penjualan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'penjualan_detail' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'delivery_order' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'pengiriman_driver' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'uang_jalan' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'expense_request' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Financial Management
            'invoice' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'receipt' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'tax_invoice' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Documents
        ];

        // Create all permissions
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::Create([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web'
                ]);
            }
        }

        // Create roles with specific permissions
        $this->createRoles($resources);
    }

    private function createRoles($resources): void
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
            'invoice' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'receipt' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'tax_invoice' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'expense_request' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'tbbm' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

            // Master Data
            'item' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'province' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'regency' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'district' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],
            'subdistrict' => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any'],

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
        $this->createRole($resources);

        $this->command->info('Roles and permissions created successfully!');
    }

    private function createRole(array $resources): void
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
        $salesResources = ['pelanggan', 'supplier', 'transaksi_penjualan', 'delivery_order', 'invoice', 'receipt', 'item', 'province', 'regency', 'district', 'subdistrict'];
        $this->assignResourcePermissions($sales, $salesResources, ['view', 'view_any', 'create', 'update', 'delete']);

        // 4. Operational - Delivery, driver, vehicle, and operational permissions
        $operational = Role::firstOrCreate([
            'name' => 'operational',
            'guard_name' => 'web',
            'deskripsi' => 'Delivery, driver, vehicle, and operational permissions only'
        ]);
        $operationalResources = ['delivery_order', 'pengiriman_driver', 'kendaraan', 'uang_jalan', 'item', 'province', 'regency', 'district', 'subdistrict'];
        $this->assignResourcePermissions($operational, $operationalResources, ['view', 'view_any', 'create', 'update', 'delete']);

        // Add view-only permissions for financial documents related to delivery orders
        $operationalFinancialResources = ['invoice', 'receipt', 'tax_invoice'];
        $this->assignResourcePermissions($operational, $operationalFinancialResources, ['view', 'view_any']);

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
        $financeResources = ['akun', 'faktur_pajak', 'invoice', 'receipt', 'tax_invoice', 'expense_request', 'tbbm', 'transaksi_penjualan', 'delivery_order'];
        $this->assignResourcePermissions($finance, $financeResources, ['view', 'view_any', 'create', 'update', 'delete']);

        // 7. Administration - User management and document permissions
        $administration = Role::firstOrCreate([
            'name' => 'administration',
            'guard_name' => 'web',
            'deskripsi' => 'User management and document permissions only'
        ]);
        $administrationResources = ['user', 'role'];
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

    /**
     * ========================================
     * SECTION 3: USERS
     * ========================================
     */
    private function seedUsers(): void
    {
        $this->command->info('Seeding users...');

        // Get required master data
        $jabatanIds = Jabatan::pluck('id')->toArray();
        $divisiIds = Divisi::pluck('id')->toArray();

        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@lrp.com',
                'password' => Hash::make('adminlrp123'),
                'id_jabatan' => $jabatanIds[0] ?? null,
                'id_divisi' => $divisiIds[0] ?? null,
            ],
            [
                'name' => 'Administrator',
                'email' => 'admin@lrp.com',
                'password' => Hash::make('adminlrp123'),
                'id_jabatan' => $jabatanIds[1] ?? null,
                'id_divisi' => $divisiIds[1] ?? null,
            ],
            [
                'name' => 'Manager Sales',
                'email' => 'manager@lrp.com',
                'password' => Hash::make('adminlrp123'),
                'id_jabatan' => $jabatanIds[1] ?? null,
                'id_divisi' => $divisiIds[1] ?? null,
            ],
            [
                'name' => 'Staff Admin',
                'email' => 'staff@lrp.com',
                'password' => Hash::make('adminlrp123'),
                'id_jabatan' => $jabatanIds[3] ?? null,
                'id_divisi' => $divisiIds[3] ?? null,
            ],
            [
                'name' => 'Driver Utama',
                'email' => 'driver@lrp.com',
                'password' => Hash::make('adminlrp123'),
                'id_jabatan' => $jabatanIds[4] ?? null,
                'id_divisi' => $divisiIds[2] ?? null,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::Create(
                [
                    'email' => $userData['email'],
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'id_jabatan' => $userData['id_jabatan'],
                    'id_divisi' => $userData['id_divisi'],
                ]
            );
        }

        $superAdmin = User::where('name', 'Super Administrator')->first();
        $admin = User::where('name', 'Administrator')->first();
        $sales = User::where('name', 'Manager Sales')->first();
        $administration = User::where('name', 'Staff Admin')->first();
        $driver = User::where('name', 'Driver Utama')->first();

        $superAdmin->assignRole('super_admin');
        $admin->assignRole('admin');
        $sales->assignRole('sales');
        $administration->assignRole('administration');
        $driver->assignRole('driver');
    }

    /**
     * ========================================
     * SECTION 4: ITEMS
     * ========================================
     */
    private function seedItems(): void
    {
        $this->command->info('Seeding items...');

        // Get existing kategori and satuan IDs
        $kategoriIds = ItemKategori::pluck('id')->toArray();
        $satuanIds = SatuanDasar::pluck('id')->toArray();

        if (empty($kategoriIds) || empty($satuanIds)) {
            $this->command->warn('Item categories or units not found. Skipping item seeding.');
            return;
        }

        $items = [
            [
                'kode' => 'BBM001',
                'name' => 'Premium',
                'description' => 'Bensin Premium RON 88',
                'id_item_jenis' => $kategoriIds[0],
                'id_satuan' => $satuanIds[0], // Liter
            ],
            [
                'kode' => 'BBM002',
                'name' => 'Pertamax',
                'description' => 'Bensin Pertamax RON 92',
                'id_item_jenis' => $kategoriIds[0],
                'id_satuan' => $satuanIds[0], // Liter
            ],
            [
                'kode' => 'BBM003',
                'name' => 'Pertalite',
                'description' => 'Bensin Pertalite RON 90',
                'id_item_jenis' => $kategoriIds[0],
                'id_satuan' => $satuanIds[0], // Liter
            ],
            [
                'kode' => 'BBM004',
                'name' => 'Solar',
                'description' => 'Solar Industri',
                'id_item_jenis' => $kategoriIds[0],
                'id_satuan' => $satuanIds[0], // Liter
            ],
            [
                'kode' => 'BBM005',
                'name' => 'Dexlite',
                'description' => 'Solar Dexlite',
                'id_item_jenis' => $kategoriIds[0],
                'id_satuan' => $satuanIds[0], // Liter
            ],
        ];

        foreach ($items as $item) {
            Item::Create($item);
        }
    }

    /**
     * ========================================
     * SECTION 5: TBBM
     * ========================================
     */
    private function seedTbbm(): void
    {
        $this->command->info('Seeding TBBM...');

        // Get existing subdistrict IDs
        $subdistrictIds = Subdistrict::pluck('id')->toArray();

        if (empty($subdistrictIds)) {
            $this->command->warn('No subdistricts found. Skipping TBBM seeding.');
            return;
        }

        $tbbmData = [
            [
                'kode' => 'TBBM001',
                'nama' => 'TBBM Plumpang',
                'pic_nama' => 'Ir. Budi Santoso',
                'pic_phone' => '021-4301234',
                'id_subdistrict' => $subdistrictIds[0] ?? '3171011001',
                'alamat' => 'Jl. Plumpang Raya, Koja, Jakarta Utara',
            ],
            [
                'kode' => 'TBBM002',
                'nama' => 'TBBM Tanjung Priok',
                'pic_nama' => 'Drs. Ahmad Fauzi',
                'pic_phone' => '021-4301235',
                'id_subdistrict' => $subdistrictIds[1] ?? '3171011002',
                'alamat' => 'Jl. Enggano Raya, Tanjung Priok, Jakarta Utara',
            ],
            [
                'kode' => 'TBBM003',
                'nama' => 'TBBM Cikampek',
                'pic_nama' => 'Dewi Sartika, S.T.',
                'pic_phone' => '0264-301234',
                'id_subdistrict' => $subdistrictIds[2] ?? '3171011003',
                'alamat' => 'Jl. Raya Cikampek KM 47, Karawang, Jawa Barat',
            ],
            [
                'kode' => 'TBBM004',
                'nama' => 'TBBM Cilacap',
                'pic_nama' => 'Rudi Hermawan, M.T.',
                'pic_phone' => '0282-531234',
                'id_subdistrict' => $subdistrictIds[3] ?? '3171011004',
                'alamat' => 'Jl. Tentara Pelajar, Cilacap, Jawa Tengah',
            ],
            [
                'kode' => 'TBBM005',
                'nama' => 'TBBM Surabaya',
                'pic_nama' => 'Maya Sari, S.E.',
                'pic_phone' => '031-3291234',
                'id_subdistrict' => $subdistrictIds[4] ?? '3171011005',
                'alamat' => 'Jl. Nilam Timur, Surabaya, Jawa Timur',
            ],
            [
                'kode' => 'TBBM006',
                'nama' => 'TBBM Semarang',
                'pic_nama' => 'Joko Widodo, S.T.',
                'pic_phone' => '024-7601234',
                'id_subdistrict' => $subdistrictIds[5] ?? '3171011006',
                'alamat' => 'Jl. Ronggowarsito, Semarang, Jawa Tengah',
            ],
        ];

        foreach ($tbbmData as $tbbm) {
            Tbbm::create($tbbm);
        }
    }

    /**
     * ========================================
     * SECTION 6: CUSTOMERS (PELANGGAN)
     * ========================================
     */
    private function seedPelanggan(): void
    {
        $this->command->info('Seeding customers...');

        // Get existing subdistrict IDs
        $subdistrictIds = Subdistrict::pluck('id')->toArray();

        if (empty($subdistrictIds)) {
            $this->command->warn('No subdistricts found. Skipping customer seeding.');
            return;
        }

        $pelangganData = [
            [
                'kode' => 'CUST001',
                'type' => 'Swasta',
                'nama' => 'PT Sinar Jaya Transport',
                'pic_nama' => 'Budi Hartono',
                'pic_phone' => '021-5551234',
                'id_subdistrict' => $subdistrictIds[0] ?? null,
                'alamat' => 'Jl. Raya Jagakarsa No. 123, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST002',
                'type' => 'Swasta',
                'nama' => 'CV Maju Bersama Logistik',
                'pic_nama' => 'Siti Rahayu',
                'pic_phone' => '021-5551235',
                'id_subdistrict' => $subdistrictIds[1] ?? null,
                'alamat' => 'Jl. Industri Raya No. 45, Bekasi',
            ],
            [
                'kode' => 'CUST003',
                'type' => 'Pemerintah',
                'nama' => 'Dinas Perhubungan Provinsi Jawa Barat',
                'pic_nama' => 'Ahmad Fauzi, S.T.',
                'pic_phone' => '0812-3456-7890',
                'id_subdistrict' => $subdistrictIds[2] ?? null,
                'alamat' => 'Jl. Merdeka No. 67, Bandung',
            ],
            [
                'kode' => 'CUST004',
                'type' => 'Swasta',
                'nama' => 'PT Energi Nusantara (Persero)',
                'pic_nama' => 'Dewi Sartika, S.T.',
                'pic_phone' => '022-7654321',
                'id_subdistrict' => $subdistrictIds[3] ?? null,
                'alamat' => 'Jl. Asia Afrika No. 89, Bandung',
            ],
            [
                'kode' => 'CUST005',
                'type' => 'Pemerintah',
                'nama' => 'Dinas Pertanian Provinsi Jawa Barat',
                'pic_nama' => 'Rudi Setiawan, S.T.',
                'pic_phone' => '031-9876543',
                'id_subdistrict' => $subdistrictIds[4] ?? null,
                'alamat' => 'Jl. Tanjungsari No. 12, Surabaya',
            ],
        ];

        foreach ($pelangganData as $pelanggan) {
            Pelanggan::Create($pelanggan);
        }
    }

    /**
     * ========================================
     * SECTION 7: CUSTOMER ADDRESSES
     * ========================================
     */
    private function seedAlamatPelanggan(): void
    {
        $this->command->info('Seeding customer addresses...');

        // Get existing customers
        $pelanggans = Pelanggan::all();

        if ($pelanggans->isEmpty()) {
            $this->command->warn('No customers found. Skipping address seeding.');
            return;
        }

        // Create addresses for each customer
        foreach ($pelanggans as $pelanggan) {
            // Create 1-2 addresses per customer
            $addressCount = rand(1, 2);

            for ($i = 0; $i < $addressCount; $i++) {
                AlamatPelanggan::create([
                    'id_pelanggan' => $pelanggan->id,
                    'alamat' => $pelanggan->alamat . ($i > 0 ? " - Cabang {$i}" : ''),
                    'location' => [
                        'lat' => -3.076628168185517 + (rand(-100, 100) / 1000000),
                        'lng' => 104.35318394703464 + (rand(-100, 100) / 1000000),
                    ],
                    'is_primary' => $i === 0, // First address is primary
                ]);
            }
        }

        // Create specific test addresses with known coordinates
        $testCustomer = Pelanggan::Create([
            'kode' => 'TEST-JKT',
            'nama' => 'PT Test Jakarta',
            'type' => 'Corporate',
            'pic_nama' => 'Test PIC',
            'pic_phone' => '021-1234567',
            'alamat' => 'Test Address',
        ]);

        $testAddresses = [
            [
                'id_pelanggan' => $testCustomer->id,
                'alamat' => 'Jl. MH Thamrin No. 1, Jakarta Pusat',
                'location' => [
                    "lat" => -6.1944,
                    "lng" => 106.8229,
                ],
                'is_primary' => true,
            ],
            [
                'id_pelanggan' => $testCustomer->id,
                'alamat' => 'Jl. Sudirman No. 52-53, Jakarta Selatan',
                'location' => [
                    'lat' => -6.2088,
                    'lng' => 106.8456,
                ],
                'is_primary' => false,
            ],
        ];

        foreach ($testAddresses as $address) {
            AlamatPelanggan::Create($address);
        }
    }

    /**
     * ========================================
     * SECTION 8: SUPPLIERS
     * ========================================
     */
    private function seedSuppliers(): void
    {
        $this->command->info('Seeding suppliers...');

        // Get existing subdistrict IDs
        $subdistrictIds = Subdistrict::pluck('id')->toArray();

        if (empty($subdistrictIds)) {
            $this->command->warn('No subdistricts found. Skipping supplier seeding.');
            return;
        }

        $supplierData = [
            [
                'kode' => 'SUPP001',
                'nama' => 'PT Pertamina (Persero)',
                'pic_nama' => 'Ir. Budi Santoso',
                'pic_phone' => '021-3815555',
                'id_subdistrict' => $subdistrictIds[0] ?? null,
                'alamat' => 'Jl. Medan Merdeka Timur No. 1A, Jakarta Pusat',
            ],
            [
                'kode' => 'SUPP002',
                'nama' => 'PT Shell Indonesia',
                'pic_nama' => 'Drs. Ahmad Fauzi',
                'pic_phone' => '021-2995000',
                'id_subdistrict' => $subdistrictIds[1] ?? null,
                'alamat' => 'Jl. Jend. Gatot Subroto Kav. 32-34, Jakarta Selatan',
            ],
            [
                'kode' => 'SUPP003',
                'nama' => 'PT Total Oil Indonesia',
                'pic_nama' => 'Dewi Sartika, S.T.',
                'pic_phone' => '021-5794888',
                'id_subdistrict' => $subdistrictIds[2] ?? null,
                'alamat' => 'Menara BCA Lt. 46-47, Jl. MH Thamrin No. 1, Jakarta Pusat',
            ],
        ];

        foreach ($supplierData as $supplier) {
            Supplier::Create($supplier);
        }
    }

    /**
     * ========================================
     * SECTION 9: VEHICLES (KENDARAAN)
     * ========================================
     */
    private function seedKendaraan(): void
    {
        $this->command->info('Seeding vehicles...');

        $kendaraanData = [
            [
                'no_pol_kendaraan' => 'B 1234 ABC',
                'merk' => 'Hino',
                'tipe' => 'Ranger FM 260 JD',
                'kapasitas' => 16000.0, // 16,000 liters
                'kapasitas_satuan' => 1, // Assuming 1 = Liter
                'tanggal_awal_valid' => Carbon::create(2023, 1, 1),
                'tanggal_akhir_valid' => Carbon::create(2025, 12, 31),
                'deskripsi' => 'Truk tangki BBM kapasitas 16KL, kondisi baik, untuk distribusi solar industri',
            ],
            [
                'no_pol_kendaraan' => 'B 5678 DEF',
                'merk' => 'Mitsubishi Fuso',
                'tipe' => 'Fighter FM 517 HS',
                'kapasitas' => 12000.0, // 12,000 liters
                'kapasitas_satuan' => 1,
                'tanggal_awal_valid' => Carbon::create(2023, 3, 15),
                'tanggal_akhir_valid' => Carbon::create(2026, 3, 14),
                'deskripsi' => 'Truk tangki BBM kapasitas 12KL, kondisi sangat baik, untuk distribusi premium',
            ],
            [
                'no_pol_kendaraan' => 'B 9012 GHI',
                'merk' => 'Isuzu',
                'tipe' => 'Giga FVZ 285',
                'kapasitas' => 20000.0, // 20,000 liters
                'kapasitas_satuan' => 1,
                'tanggal_awal_valid' => Carbon::create(2022, 6, 1),
                'tanggal_akhir_valid' => Carbon::create(2025, 5, 31),
                'deskripsi' => 'Truk tangki BBM kapasitas 20KL, kondisi baik, untuk distribusi pertamax',
            ],
        ];

        foreach ($kendaraanData as $kendaraan) {
            Kendaraan::Create($kendaraan);
        }
    }

    /**
     * ========================================
     * SECTION 10: EXPENSE REQUESTS
     * ========================================
     */
    private function seedExpenseRequests(): void
    {
        $this->command->info('Seeding expense requests...');

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Skipping expense request seeding.');
            return;
        }

        // Create expense-requests directory if it doesn't exist
        if (!Storage::disk('public')->exists('expense-requests')) {
            Storage::disk('public')->makeDirectory('expense-requests');
        }

        $categories = [
            'tank_truck_maintenance',
            'license_fee',
            'business_travel',
            'utilities',
            'other'
        ];

        $statuses = [
            'draft' => 20,
            'submitted' => 30,
            'under_review' => 15,
            'approved' => 20,
            'rejected' => 10,
            'paid' => 5
        ];

        $priorities = ['low', 'medium', 'high', 'urgent'];

        $maintenanceItems = [
            'Engine Oil Change',
            'Brake System Maintenance',
            'Tire Replacement',
            'Transmission Service',
            'Hydraulic System Repair',
            'Tank Cleaning Service',
            'Safety Equipment Inspection',
            'Electrical System Repair'
        ];

        $licenseItems = [
            'Microsoft Office 365 License',
            'Antivirus Software License',
            'Accounting Software License',
            'Fleet Management Software',
            'GPS Tracking System License',
            'Business Operating License Renewal',
            'Environmental Permit Renewal',
            'Transport License Renewal'
        ];

        $travelItems = [
            'Client Meeting in Jakarta',
            'Training Seminar in Surabaya',
            'Business Conference in Bandung',
            'Supplier Visit in Medan',
            'Equipment Installation in Palembang',
            'Customer Service Visit',
            'Market Research Trip',
            'Partnership Meeting'
        ];

        $utilityItems = [
            'Office Electricity Bill',
            'Water Supply Bill',
            'Internet & Telephone Bill',
            'Warehouse Utilities',
            'Security System Maintenance',
            'Cleaning Service',
            'Waste Management Service',
            'HVAC System Maintenance'
        ];

        $otherItems = [
            'Office Supplies Purchase',
            'Marketing Materials',
            'Employee Training Costs',
            'Insurance Premium',
            'Legal Consultation Fees',
            'Audit Services',
            'Equipment Rental',
            'Facility Maintenance'
        ];

        $totalRequests = 50;
        $createdCount = 0;

        foreach ($statuses as $status => $percentage) {
            $count = round(($percentage / 100) * $totalRequests);

            for ($i = 0; $i < $count; $i++) {
                $category = $categories[array_rand($categories)];
                $requestedBy = $users->random();
                $approvedBy = $status === 'draft' ? null : $users->random();

                // Generate title and description based on category
                [$title, $description] = $this->generateTitleAndDescription($category, $maintenanceItems, $licenseItems, $travelItems, $utilityItems, $otherItems);

                $requestedAmount = $this->generateAmount($category);
                $approvedAmount = in_array($status, ['approved', 'paid']) ? $requestedAmount * (0.8 + (rand(0, 40) / 100)) : null;

                $requestDate = now()->subDays(rand(1, 90));
                $neededByDate = $requestDate->copy()->addDays(rand(7, 30));

                // Generate supporting documents
                $supportingDocs = $this->generateSupportingDocuments($category);

                $expenseRequest = ExpenseRequest::create([
                    'request_number' => ExpenseRequest::generateRequestNumber($category),
                    'category' => $category,
                    'user_id' => $requestedBy->id,
                    'title' => $title,
                    'description' => $description,
                    'requested_amount' => $requestedAmount,
                    'approved_amount' => $approvedAmount,
                    'status' => $status,
                    'priority' => $priorities[array_rand($priorities)],
                    'requested_date' => $requestDate,
                    'needed_by_date' => $neededByDate,
                    'justification' => $this->generateJustification($category),
                    'supporting_documents' => $supportingDocs,
                    'requested_by' => $requestedBy->id,
                    'approved_by' => $approvedBy?->id,
                    'submitted_at' => $status !== 'draft' ? $requestDate->copy()->addHours(rand(1, 24)) : null,
                    'reviewed_at' => in_array($status, ['approved', 'rejected', 'paid']) ? $requestDate->copy()->addDays(rand(1, 5)) : null,
                    'approved_at' => in_array($status, ['approved', 'paid']) ? $requestDate->copy()->addDays(rand(1, 7)) : null,
                    'paid_at' => $status === 'paid' ? $requestDate->copy()->addDays(rand(7, 14)) : null,
                    'approval_notes' => in_array($status, ['approved', 'paid']) ? 'Approved as per company policy and budget allocation.' : null,
                    'rejection_reason' => $status === 'rejected' ? 'Budget constraints for this period. Please resubmit next quarter.' : null,
                    'cost_center' => $this->generateCostCenter($category),
                    'budget_code' => $this->generateBudgetCode($category),
                ]);

                $createdCount++;
            }
        }

        $this->command->info("ExpenseRequest seeder completed! Created {$createdCount} expense requests.");
    }

    private function generateTitleAndDescription($category, $maintenanceItems, $licenseItems, $travelItems, $utilityItems, $otherItems): array
    {
        return match ($category) {
            'tank_truck_maintenance' => [
                $maintenanceItems[array_rand($maintenanceItems)],
                'Regular maintenance required for tank truck fleet to ensure operational safety and compliance with transportation regulations.'
            ],
            'license_fee' => [
                $licenseItems[array_rand($licenseItems)],
                'Annual license renewal required for continued business operations and software usage.'
            ],
            'business_travel' => [
                $travelItems[array_rand($travelItems)],
                'Business travel expenses including transportation, accommodation, and meals for official company business.'
            ],
            'utilities' => [
                $utilityItems[array_rand($utilityItems)],
                'Monthly utility expenses for office and warehouse facilities operations.'
            ],
            'other' => [
                $otherItems[array_rand($otherItems)],
                'Miscellaneous business expense required for operational efficiency and business growth.'
            ],
        };
    }

    private function generateAmount($category): float
    {
        return match ($category) {
            'tank_truck_maintenance' => rand(5000000, 50000000), // 5M - 50M
            'license_fee' => rand(1000000, 15000000), // 1M - 15M
            'business_travel' => rand(2000000, 10000000), // 2M - 10M
            'utilities' => rand(3000000, 20000000), // 3M - 20M
            'other' => rand(1000000, 25000000), // 1M - 25M
        };
    }

    private function generateJustification($category): string
    {
        return match ($category) {
            'tank_truck_maintenance' => 'Essential for maintaining fleet safety standards and preventing costly breakdowns that could disrupt delivery schedules.',
            'license_fee' => 'Required for legal compliance and continued access to essential business software and systems.',
            'business_travel' => 'Necessary for maintaining client relationships, exploring new business opportunities, and staff development.',
            'utilities' => 'Essential operational expenses for maintaining office and warehouse facilities.',
            'other' => 'Required for supporting business operations and maintaining competitive advantage in the market.',
        };
    }

    private function generateCostCenter($category): string
    {
        return match ($category) {
            'tank_truck_maintenance' => 'Operations',
            'license_fee' => 'IT & Administration',
            'business_travel' => 'Sales & Marketing',
            'utilities' => 'Facilities',
            'other' => 'General & Administrative',
        };
    }

    private function generateBudgetCode($category): string
    {
        $year = now()->year;
        return match ($category) {
            'tank_truck_maintenance' => "MAINT-{$year}",
            'license_fee' => "LIC-{$year}",
            'business_travel' => "TRAVEL-{$year}",
            'utilities' => "UTIL-{$year}",
            'other' => "MISC-{$year}",
        };
    }

    private function generateSupportingDocuments($category): array
    {
        $docs = [];
        $docCount = rand(1, 3);

        for ($i = 0; $i < $docCount; $i++) {
            $filename = match ($category) {
                'tank_truck_maintenance' => "maintenance-quote-{$i}.pdf",
                'license_fee' => "license-invoice-{$i}.pdf",
                'business_travel' => "travel-estimate-{$i}.pdf",
                'utilities' => "utility-bill-{$i}.pdf",
                'other' => "supporting-doc-{$i}.pdf",
            };

            // Create dummy file
            $content = "This is a dummy supporting document for expense request.\nCategory: {$category}\nDocument: {$filename}";
            Storage::disk('public')->put("expense-requests/{$filename}", $content);

            $docs[] = "expense-requests/{$filename}";
        }

        return $docs;
    }

    /**
     * ========================================
     * SECTION 11: SALES AND DELIVERY ORDERS
     * ========================================
     */
    private function seedSalesAndDeliveryOrders(): void
    {
        $this->command->info('Seeding sales and delivery orders...');

        $customers = Pelanggan::all();
        $items = Item::all();
        $tbbms = Tbbm::all();
        $vehicles = Kendaraan::all();
        $users = User::all();
        $drivers = User::where('id_jabatan', '5')->get();

        if ($customers->isEmpty() || $items->isEmpty() || $tbbms->isEmpty() || $vehicles->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Missing required data for sales orders. Skipping.');
            return;
        }

        // Create 10 sales orders with varying statuses
        for ($i = 1; $i <= 10; $i++) {
            $customer = $customers->random();
            $tbbm = $tbbms->random();
            $user = $users->random();

            $salesOrder = TransaksiPenjualan::create([
                'kode' => 'SO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'tipe' => ['dagang', 'jasa'][rand(0, 1)],
                'id_pelanggan' => $customer->id,
                'tanggal' => now()->subDays(rand(1, 30)),
                'id_alamat_pelanggan' => $customer->alamatPelanggan->random()->id,
                'nomor_po' => 'PO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nomor_sph' => 'SPH-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'data_dp' => rand(1000000, 5000000),
                'top_pembayaran' => [7, 14, 30, 45][rand(0, 3)],
                'id_tbbm' => $tbbm->id,
                'created_by' => $user->id,
            ]);

            // Create sales order details
            $itemCount = rand(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $item = $items->random();
                PenjualanDetail::create([
                    'id_transaksi_penjualan' => $salesOrder->id,
                    'id_item' => $item->id,
                    'volume_item' => rand(1000, 5000),
                    'harga_jual' => rand(10000, 20000),
                    'created_by' => $user->id,
                ]);
            }

            // Create delivery orders for some sales orders
            if (rand(0, 1)) {
                $vehicle = $vehicles->random();
                $driver = $drivers->isNotEmpty() ? $drivers->random() : $users->random();

                // Calculate volume for this delivery order
                $totalSoVolume = $salesOrder->penjualanDetails->sum('volume_item');
                $volumeDo = rand(500, min($totalSoVolume, 3000)); // Random volume but not exceeding SO volume
                $sisaVolume = $totalSoVolume - $volumeDo;

                $deliveryOrder = DeliveryOrder::create([
                    'kode' => 'DO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'id_transaksi' => $salesOrder->id,
                    'id_user' => $driver->id,
                    'id_kendaraan' => $vehicle->id,
                    'tanggal_delivery' => now()->subDays(rand(1, 20)),
                    'no_segel' => 'SEAL-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'status_muat' => ['pending', 'muat', 'selesai'][rand(0, 2)],
                    'waktu_muat' => now()->subDays(rand(1, 20))->addHours(8),
                    'waktu_selesai_muat' => now()->subDays(rand(1, 20))->addHours(10),
                    'volume_do' => $volumeDo,
                    'sisa_volume_do' => $sisaVolume,
                    'created_by' => $user->id,
                ]);

                // Create driver allowance
                UangJalan::create([
                    'id_do' => $deliveryOrder->id,
                    'nominal' => rand(500000, 1000000),
                    'status_kirim' => ['pending', 'kirim', 'ditolak'][rand(0, 1)],
                    'status_terima' => ['pending', 'terima', 'ditolak'][rand(0, 1)],
                    'id_user' => $driver->id,
                    'created_by' => $user->id,
                ]);

                // Create delivery progress for completed deliveries
                if (rand(0, 1)) {
                    PengirimanDriver::create([
                        'id_do' => $deliveryOrder->id,
                        'waktu_mulai' => now()->subDays(rand(1, 15))->addHours(11),
                        'waktu_tiba' => now()->subDays(rand(1, 15))->addHours(15),
                        'waktu_pool_arrival' => now()->subDays(rand(1, 15))->addHours(17),
                        'totalisator_awal' => rand(20000, 30000),
                        'totalisator_tiba' => rand(22000, 32000),
                        'totalisator_pool_return' => rand(20000, 30000),
                        'created_by' => $user->id,
                    ]);
                }
            }
        }
    }



    /**
     * ========================================
     * SECTION 12: TEST DATA
     * ========================================
     */
    private function seedTestData(): void
    {
        $this->command->info('Seeding test data...');

        // This section includes specific test data for timeline and other features
        // Reusing existing users from UserSeeder
        $user = User::where('email', 'admin@lrp.com')->first();
        $driver = User::where('email', 'driver@lrp.com')->first();

        if (!$user || !$driver) {
            $this->command->warn('Required test users not found. Skipping test data.');
            return;
        }

        // Create timeline test customer
        $customer = Pelanggan::Create([
            'kode' => 'TIMELINE-CUST-001',
            'type' => 'corporate',
            'nama' => 'Timeline Test Customer',
            'pic_nama' => 'Test PIC',
            'pic_phone' => '081234567890',
            'alamat' => 'Test Customer Address',
            'created_by' => $user->id,
        ]);

        // Create timeline test fuel item
        $fuelItem = Item::Create([
            'kode' => 'TIMELINE-FUEL-001',
            'name' => 'Timeline Test Premium',
            'description' => 'Test fuel for timeline',
            'id_item_jenis' => ItemKategori::first()?->id ?? 1,
            'id_satuan' => SatuanDasar::first()?->id ?? 1,
            'created_by' => $user->id,
        ]);

        // Create timeline test TBBM
        $tbbm = Tbbm::Create([
            'kode' => 'TIMELINE-TBBM-001',
            'nama' => 'Timeline Test TBBM',
            'alamat' => 'Test TBBM Address',
            'created_by' => $user->id,
        ]);

        // Create timeline test vehicle
        $vehicle = Kendaraan::Create([
            'no_pol_kendaraan' => 'TIMELINE-001',
            'merk' => 'Hino',
            'tipe' => 'Ranger',
            'kapasitas' => 8000,
            'kapasitas_satuan' => 1,
            'tanggal_awal_valid' => now(),
            'tanggal_akhir_valid' => now()->addYear(),
            'deskripsi' => 'Test vehicle for timeline',
        ]);

        // Create comprehensive test sales order with complete timeline
        $salesOrder = TransaksiPenjualan::Create([
            'kode' => 'TIMELINE-SO-001',
            'tipe' => 'dagang',
            'tanggal' => now()->subDays(5),
            'id_pelanggan' => $customer->id,
            'id_alamat_pelanggan' => $customer->alamatPelanggan->first()?->id ?? 1,
            'nomor_po' => 'TIMELINE-PO-001',
            'nomor_sph' => 'TIMELINE-SPH-001',
            'data_dp' => 1000000,
            'top_pembayaran' => 30,
            'id_tbbm' => $tbbm->id,
            'created_by' => $user->id,
        ]);

        // Create sales order detail
        PenjualanDetail::Create([
            'id_transaksi_penjualan' => $salesOrder->id,
            'id_item' => $fuelItem->id,
            'volume_item' => 2000,
            'harga_jual' => 15000,
            'created_by' => $user->id,
        ]);

        $this->command->info('Test data seeding completed.');

        // Create comprehensive test sales order with complete timeline
        $comprehensiveSalesOrder = TransaksiPenjualan::Create([
            'kode' => 'TIMELINE-SO-002',
            'tipe' => 'dagang',
            'tanggal' => now()->subDays(10),
            'id_pelanggan' => $customer->id,
            'id_alamat_pelanggan' => $customer->alamatPelanggan->first()?->id ?? 1,
            'nomor_po' => 'TIMELINE-PO-002',
            'nomor_sph' => 'TIMELINE-SPH-002',
            'data_dp' => 1000000,
            'top_pembayaran' => 30,
            'id_tbbm' => $tbbm->id,
            'created_by' => $user->id,
        ]);
        // Create sales order detail
        PenjualanDetail::Create([
            'id_transaksi_penjualan' => $comprehensiveSalesOrder->id,
            'id_item' => $fuelItem->id,
            'volume_item' => 2000,
            'harga_jual' => 15000,
            'created_by' => $user->id,
        ]);

        // Create delivery order
        $deliveryOrder = DeliveryOrder::create([
            'kode' => 'DO-COMP-001',
            'id_transaksi' => $comprehensiveSalesOrder->id,
            'id_user' => $driver->id,
            'id_kendaraan' => $vehicle->id,
            'tanggal_delivery' => now()->subDays(10),
            'no_segel' => 'SEAL-COMP-001',
            'status_muat' => 'selesai',
            'waktu_muat' => now()->subDays(10)->addHours(8),
            'waktu_selesai_muat' => now()->subDays(10)->addHours(10),
            'volume_do' => 2000, // Full volume from SO
            'sisa_volume_do' => 0, // No remaining volume
            'created_by' => $user->id,
        ]);

        // Create driver allowance
        UangJalan::create([
            'id_do' => $deliveryOrder->id,
            'nominal' => 750000,
            'status_kirim' => 'kirim',
            'status_terima' => 'terima',
            'id_user' => $driver->id,
            'created_by' => $user->id,
        ]);

        // Create delivery progress
        PengirimanDriver::create([
            'id_do' => $deliveryOrder->id,
            'waktu_mulai' => now()->subDays(10)->addHours(11),
            'waktu_tiba' => now()->subDays(10)->addHours(15),
            'waktu_pool_arrival' => now()->subDays(10)->addHours(17),
            'totalisator_awal' => 20000,
            'totalisator_tiba' => 22000,
            'totalisator_pool_return' => 20000,
            'created_by' => $user->id,
        ]);
        $this->command->info('Test data seeding completed.');
        $this->command->info('Comprehensive database seeding completed successfully!');
    }

    /**
     * ========================================
     * SECTION 12: FINANCIAL MANAGEMENT
     * ========================================
     */
    private function seedFinancialManagement(): void
    {
        $this->command->info('Seeding financial management data...');

        // Get existing data
        $deliveryOrders = DeliveryOrder::with('transaksi.pelanggan')->get();
        $users = User::all();

        if ($deliveryOrders->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Missing required data for financial management. Skipping.');
            return;
        }

        $adminUser = $users->where('email', 'superadmin@lrp.com')->first() ?? $users->first();

        foreach ($deliveryOrders as $deliveryOrder) {
            if (!$deliveryOrder->transaksi || !$deliveryOrder->transaksi->pelanggan) {
                continue;
            }

            $transaksi = $deliveryOrder->transaksi;
            $pelanggan = $transaksi->pelanggan;

            // Calculate amounts from sales details
            $subtotal = $transaksi->penjualanDetails->sum(function ($detail) {
                return $detail->volume_item * $detail->harga_jual;
            });

            if ($subtotal <= 0) {
                $subtotal = rand(5000000, 50000000); // Fallback random amount
            }

            $taxRate = 11; // 11% PPN
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Create Invoice
            $invoice = Invoice::create([
                'nomor_invoice' => 'INV-' . str_pad($deliveryOrder->id, 6, '0', STR_PAD_LEFT),
                'id_do' => $deliveryOrder->id,
                'id_transaksi' => $transaksi->id,
                'tanggal_invoice' => $deliveryOrder->tanggal_delivery ?? now()->subDays(rand(1, 30)),
                'tanggal_jatuh_tempo' => ($deliveryOrder->tanggal_delivery ?? now())->addDays(30),
                'nama_pelanggan' => $pelanggan->nama,
                'alamat_pelanggan' => $pelanggan->alamat,
                'npwp_pelanggan' => $pelanggan->npwp,
                'subtotal' => $subtotal,
                'total_pajak' => $taxAmount,
                'total_invoice' => $totalAmount,
                'total_terbayar' => 0,
                'sisa_tagihan' => $totalAmount,
                'status' => ['draft', 'sent', 'paid', 'overdue'][rand(0, 3)],
                'catatan' => 'Invoice untuk delivery order ' . $deliveryOrder->kode,
                'created_by' => $adminUser->id,
            ]);

            // Create Tax Invoice (for some invoices)
            if (rand(0, 1)) {
                TaxInvoice::create([
                    'nomor_tax_invoice' => 'FP-' . str_pad($deliveryOrder->id, 6, '0', STR_PAD_LEFT),
                    'id_invoice' => $invoice->id,
                    'id_do' => $deliveryOrder->id,
                    'id_transaksi' => $transaksi->id,
                    'tanggal_tax_invoice' => $invoice->tanggal_invoice,
                    'nama_pelanggan' => $pelanggan->nama,
                    'alamat_pelanggan' => $pelanggan->alamat,
                    'npwp_pelanggan' => $pelanggan->npwp,
                    'nama_perusahaan' => 'PT. Logistik Riau Prima',
                    'alamat_perusahaan' => 'Jl. Riau Prima No. 123, Pekanbaru',
                    'npwp_perusahaan' => '01.234.567.8-901.000',
                    'dasar_pengenaan_pajak' => $subtotal,
                    'tarif_pajak' => $taxRate,
                    'pajak_pertambahan_nilai' => $taxAmount,
                    'total_tax_invoice' => $totalAmount,
                    'status' => ['draft', 'submitted', 'approved'][rand(0, 2)],
                    'catatan' => 'Faktur pajak untuk invoice ' . $invoice->nomor_invoice,
                    'created_by' => $adminUser->id,
                ]);
            }

            // Create Receipts (for paid/partially paid invoices)
            if (in_array($invoice->status, ['paid']) || rand(0, 2) == 0) {
                $paymentMethods = ['transfer', 'cash', 'check', 'giro'];
                $numPayments = $invoice->status === 'paid' ? rand(1, 3) : rand(1, 2);
                $totalPaid = 0;

                for ($i = 0; $i < $numPayments; $i++) {
                    $remainingAmount = $totalAmount - $totalPaid;
                    if ($remainingAmount <= 0) break;

                    $paymentAmount = $i === $numPayments - 1 && $invoice->status === 'paid'
                        ? $remainingAmount
                        : rand(1000000, min($remainingAmount, $totalAmount * 0.7));

                    $adminFee = $paymentAmount * 0.001; // 0.1% admin fee

                    Receipt::create([
                        'nomor_receipt' => 'RCP-' . str_pad($deliveryOrder->id, 6, '0', STR_PAD_LEFT) . '-' . ($i + 1),
                        'id_invoice' => $invoice->id,
                        'id_do' => $deliveryOrder->id,
                        'id_transaksi' => $transaksi->id,
                        'tanggal_receipt' => $invoice->tanggal_invoice->addDays(rand(1, 15)),
                        'tanggal_pembayaran' => $invoice->tanggal_invoice->addDays(rand(1, 15)),
                        'metode_pembayaran' => $paymentMethods[array_rand($paymentMethods)],
                        'referensi_pembayaran' => 'REF-' . strtoupper(uniqid()),
                        'jumlah_pembayaran' => $paymentAmount,
                        'biaya_admin' => $adminFee,
                        'total_diterima' => $paymentAmount - $adminFee,
                        'status' => ['pending', 'confirmed'][rand(0, 1)],
                        'catatan' => 'Pembayaran untuk invoice ' . $invoice->nomor_invoice,
                        'bank_pengirim' => ['BCA', 'Mandiri', 'BNI', 'BRI'][rand(0, 3)],
                        'bank_penerima' => 'BCA',
                        'created_by' => $adminUser->id,
                    ]);

                    $totalPaid += $paymentAmount;
                }

                // Update invoice payment status
                $invoice->update([
                    'total_terbayar' => $totalPaid,
                    'sisa_tagihan' => $totalAmount - $totalPaid,
                    'status' => $totalPaid >= $totalAmount ? 'paid' : 'sent'
                ]);
            }
        }

        $this->command->info('Financial management data seeded successfully!');
    }
}
