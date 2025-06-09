<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Subdistrict;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing subdistrict IDs
        $subdistrictIds = Subdistrict::pluck('id')->toArray();

        $supplierData = [
            [
                'kode' => 'SUPP001',
                'nama' => 'PT Pertamina (Persero)',
                'pic_nama' => 'Ir. Budi Santoso',
                'pic_phone' => '021-3815555',
                'id_subdistrict' => $subdistrictIds[0] ?? '3171011001',
                'alamat' => 'Jl. Medan Merdeka Timur No. 1A, Jakarta Pusat',
            ],
            [
                'kode' => 'SUPP002',
                'nama' => 'PT Shell Indonesia',
                'pic_nama' => 'Drs. Ahmad Fauzi',
                'pic_phone' => '021-2995000',
                'id_subdistrict' => $subdistrictIds[1] ?? '3171011002',
                'alamat' => 'Jl. Jend. Gatot Subroto Kav. 32-34, Jakarta Selatan',
            ],
            [
                'kode' => 'SUPP003',
                'nama' => 'PT Total Oil Indonesia',
                'pic_nama' => 'Dewi Sartika, S.T.',
                'pic_phone' => '021-5794888',
                'id_subdistrict' => $subdistrictIds[2] ?? '3171011003',
                'alamat' => 'Menara BCA Lt. 46-47, Jl. MH Thamrin No. 1, Jakarta Pusat',
            ],
            [
                'kode' => 'SUPP004',
                'nama' => 'PT Chevron Pacific Indonesia',
                'pic_nama' => 'Rudi Hermawan, M.T.',
                'pic_phone' => '021-2992888',
                'id_subdistrict' => $subdistrictIds[3] ?? '3171011004',
                'alamat' => 'Sentral Senayan II Lt. 20, Jl. Asia Afrika No. 8, Jakarta Pusat',
            ],
            [
                'kode' => 'SUPP005',
                'nama' => 'PT Vivo Energy Indonesia',
                'pic_nama' => 'Maya Sari, S.E.',
                'pic_phone' => '021-5140888',
                'id_subdistrict' => $subdistrictIds[4] ?? '3171011005',
                'alamat' => 'Wisma Mulia Lt. 39, Jl. Jend. Gatot Subroto Kav. 42, Jakarta Selatan',
            ],
            [
                'kode' => 'SUPP006',
                'nama' => 'PT AKR Corporindo Tbk',
                'pic_nama' => 'Joko Widodo, S.T.',
                'pic_phone' => '021-4585588',
                'id_subdistrict' => $subdistrictIds[5] ?? '3171011006',
                'alamat' => 'Jl. Panjang No. 26, Kebon Jeruk, Jakarta Barat',
            ],
            [
                'kode' => 'SUPP007',
                'nama' => 'PT Kilang Pertamina Internasional',
                'pic_nama' => 'Rina Susanti, M.M.',
                'pic_phone' => '021-3815777',
                'id_subdistrict' => $subdistrictIds[0] ?? '3171011001',
                'alamat' => 'Jl. Medan Merdeka Timur No. 6, Jakarta Pusat',
            ],
            [
                'kode' => 'SUPP008',
                'nama' => 'PT Elnusa Petrofin',
                'pic_nama' => 'Bambang Sutrisno, S.T.',
                'pic_phone' => '021-7918888',
                'id_subdistrict' => $subdistrictIds[1] ?? '3171011002',
                'alamat' => 'Graha Elnusa Lt. 5, Jl. TB Simatupang Kav. 1B, Jakarta Selatan',
            ],
        ];

        foreach ($supplierData as $supplier) {
            Supplier::create($supplier);
        }
    }
}
