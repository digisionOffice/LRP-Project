<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
use App\Models\Role;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Provinces (Sample data)
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

        foreach ($provinces as $province) {
            Province::firstOrCreate(['id' => $province['id']], $province);
        }

        // Sample Regencies for Jakarta
        $regencies = [
            ['id' => '3101', 'province_id' => '31', 'name' => 'Kepulauan Seribu'],
            ['id' => '3171', 'province_id' => '31', 'name' => 'Jakarta Selatan'],
            ['id' => '3172', 'province_id' => '31', 'name' => 'Jakarta Timur'],
            ['id' => '3173', 'province_id' => '31', 'name' => 'Jakarta Pusat'],
            ['id' => '3174', 'province_id' => '31', 'name' => 'Jakarta Barat'],
            ['id' => '3175', 'province_id' => '31', 'name' => 'Jakarta Utara'],
        ];

        foreach ($regencies as $regency) {
            Regency::firstOrCreate(['id' => $regency['id']], $regency);
        }

        // Sample Districts for Jakarta Selatan
        $districts = [
            ['id' => '3171010', 'regency_id' => '3171', 'name' => 'Jagakarsa'],
            ['id' => '3171020', 'regency_id' => '3171', 'name' => 'Pasar Minggu'],
            ['id' => '3171030', 'regency_id' => '3171', 'name' => 'Cilandak'],
            ['id' => '3171040', 'regency_id' => '3171', 'name' => 'Pesanggrahan'],
            ['id' => '3171050', 'regency_id' => '3171', 'name' => 'Kebayoran Lama'],
            ['id' => '3171060', 'regency_id' => '3171', 'name' => 'Kebayoran Baru'],
            ['id' => '3171070', 'regency_id' => '3171', 'name' => 'Mampang Prapatan'],
            ['id' => '3171080', 'regency_id' => '3171', 'name' => 'Pancoran'],
            ['id' => '3171090', 'regency_id' => '3171', 'name' => 'Tebet'],
            ['id' => '3171100', 'regency_id' => '3171', 'name' => 'Setia Budi'],
        ];

        foreach ($districts as $district) {
            District::firstOrCreate(['id' => $district['id']], $district);
        }

        // Sample Subdistricts for Jagakarsa
        $subdistricts = [
            ['id' => '3171011001', 'district_id' => '3171010', 'name' => 'Jagakarsa'],
            ['id' => '3171011002', 'district_id' => '3171010', 'name' => 'Srengseng Sawah'],
            ['id' => '3171011003', 'district_id' => '3171010', 'name' => 'Cipedak'],
            ['id' => '3171011004', 'district_id' => '3171010', 'name' => 'Lenteng Agung'],
            ['id' => '3171011005', 'district_id' => '3171010', 'name' => 'Tanjung Barat'],
            ['id' => '3171011006', 'district_id' => '3171010', 'name' => 'Ciganjur'],
        ];

        foreach ($subdistricts as $subdistrict) {
            Subdistrict::firstOrCreate(['id' => $subdistrict['id']], $subdistrict);
        }

        // Seed Entitas Tipe
        $entitasTipes = [
            ['nama' => 'Kantor Pusat'],
            ['nama' => 'Kantor Cabang'],
            ['nama' => 'Gudang'],
            ['nama' => 'TBBM'],
        ];

        foreach ($entitasTipes as $tipe) {
            EntitasTipe::firstOrCreate(['nama' => $tipe['nama']], $tipe);
        }

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
            Jabatan::firstOrCreate(['nama' => $jabatan['nama']], $jabatan);
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
            Divisi::firstOrCreate(['nama' => $divisi['nama']], $divisi);
        }

        // Seed Item Kategori
        $itemKategoris = [
            ['nama' => 'BBM', 'deskripsi' => 'Bahan Bakar Minyak'],
            ['nama' => 'Solar', 'deskripsi' => 'Solar Industri'],
            ['nama' => 'Pertamax', 'deskripsi' => 'Pertamax'],
            ['nama' => 'Premium', 'deskripsi' => 'Premium'],
            ['nama' => 'Pertalite', 'deskripsi' => 'Pertalite'],
        ];

        foreach ($itemKategoris as $kategori) {
            ItemKategori::firstOrCreate(['nama' => $kategori['nama']], $kategori);
        }

        // Seed Satuan Dasar
        $satuanDasars = [
            ['kode' => 'LTR', 'nama' => 'Liter', 'deskripsi' => 'Satuan Volume Liter'],
            ['kode' => 'KL', 'nama' => 'Kiloliter', 'deskripsi' => 'Satuan Volume Kiloliter'],
            ['kode' => 'KG', 'nama' => 'Kilogram', 'deskripsi' => 'Satuan Berat Kilogram'],
            ['kode' => 'TON', 'nama' => 'Ton', 'deskripsi' => 'Satuan Berat Ton'],
            ['kode' => 'PCS', 'nama' => 'Pieces', 'deskripsi' => 'Satuan Buah'],
        ];

        foreach ($satuanDasars as $satuan) {
            SatuanDasar::firstOrCreate(['kode' => $satuan['kode']], $satuan);
        }

        // Seed Chart of Accounts
        $akuns = [
            ['kode_akun' => '1100', 'nama_akun' => 'Kas', 'tipe_akun' => 'aktiva'],
            ['kode_akun' => '1200', 'nama_akun' => 'Bank', 'tipe_akun' => 'aktiva'],
            ['kode_akun' => '1300', 'nama_akun' => 'Piutang Dagang', 'tipe_akun' => 'aktiva'],
            ['kode_akun' => '1400', 'nama_akun' => 'Persediaan Barang', 'tipe_akun' => 'aktiva'],
            ['kode_akun' => '1500', 'nama_akun' => 'Kendaraan', 'tipe_akun' => 'aktiva'],
            ['kode_akun' => '2100', 'nama_akun' => 'Utang Dagang', 'tipe_akun' => 'kewajiban'],
            ['kode_akun' => '2200', 'nama_akun' => 'Utang Bank', 'tipe_akun' => 'kewajiban'],
            ['kode_akun' => '3100', 'nama_akun' => 'Modal Saham', 'tipe_akun' => 'modal'],
            ['kode_akun' => '4100', 'nama_akun' => 'Pendapatan Penjualan', 'tipe_akun' => 'pendapatan'],
            ['kode_akun' => '5100', 'nama_akun' => 'Harga Pokok Penjualan', 'tipe_akun' => 'biaya'],
            ['kode_akun' => '5200', 'nama_akun' => 'Biaya Operasional', 'tipe_akun' => 'biaya'],
        ];

        foreach ($akuns as $akun) {
            Akun::firstOrCreate(['kode_akun' => $akun['kode_akun']], $akun);
        }

        // // Seed Roles
        // $roles = [
        //     ['name' => 'Super Admin', 'deskripsi' => 'Full access to all modules'],
        //     ['name' => 'Admin', 'deskripsi' => 'Administrative access'],
        //     ['name' => 'Sales', 'deskripsi' => 'Sales module access'],
        //     ['name' => 'Operasional', 'deskripsi' => 'Operational module access'],
        //     ['name' => 'Driver', 'deskripsi' => 'Driver module access'],
        //     ['name' => 'Keuangan', 'deskripsi' => 'Finance module access'],
        //     ['name' => 'Administrasi', 'deskripsi' => 'Administration module access'],
        // ];

        // foreach ($roles as $role) {
        //     Role::firstOrCreate(['name' => $role['name']], $role);
        // }
    }
}
