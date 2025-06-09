<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pelanggan;
use App\Models\Subdistrict;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing subdistrict IDs
        $subdistrictIds = Subdistrict::pluck('id')->toArray();

        $pelangganData = [
            [
                'kode' => 'CUST001',
                'type' => 'Corporate',
                'nama' => 'PT Sinar Jaya Transport',
                'pic_nama' => 'Budi Hartono',
                'pic_phone' => '021-5551234',
                'id_subdistrict' => $subdistrictIds[0] ?? '3171011001',
                'alamat' => 'Jl. Raya Jagakarsa No. 123, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST002',
                'type' => 'Corporate',
                'nama' => 'CV Maju Bersama Logistik',
                'pic_nama' => 'Siti Rahayu',
                'pic_phone' => '021-5551235',
                'id_subdistrict' => $subdistrictIds[1] ?? '3171011002',
                'alamat' => 'Jl. Srengseng Sawah Raya No. 45, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST003',
                'type' => 'Individual',
                'nama' => 'Ahmad Subandi',
                'pic_nama' => 'Ahmad Subandi',
                'pic_phone' => '081234567890',
                'id_subdistrict' => $subdistrictIds[2] ?? '3171011003',
                'alamat' => 'Jl. Cipedak Raya No. 67, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST004',
                'type' => 'Corporate',
                'nama' => 'PT Indah Kargo Nusantara',
                'pic_nama' => 'Dewi Lestari',
                'pic_phone' => '021-5551236',
                'id_subdistrict' => $subdistrictIds[3] ?? '3171011004',
                'alamat' => 'Jl. Lenteng Agung Raya No. 89, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST005',
                'type' => 'Corporate',
                'nama' => 'PT Bayu Samudra Ekspedisi',
                'pic_nama' => 'Rudi Setiawan',
                'pic_phone' => '021-5551237',
                'id_subdistrict' => $subdistrictIds[4] ?? '3171011005',
                'alamat' => 'Jl. Tanjung Barat No. 12, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST006',
                'type' => 'Individual',
                'nama' => 'Hendra Wijaya',
                'pic_nama' => 'Hendra Wijaya',
                'pic_phone' => '081234567891',
                'id_subdistrict' => $subdistrictIds[5] ?? '3171011006',
                'alamat' => 'Jl. Ciganjur Raya No. 34, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST007',
                'type' => 'Corporate',
                'nama' => 'CV Sejahtera Mandiri',
                'pic_nama' => 'Maya Sari',
                'pic_phone' => '021-5551238',
                'id_subdistrict' => $subdistrictIds[0] ?? '3171011001',
                'alamat' => 'Jl. Jagakarsa Raya No. 56, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST008',
                'type' => 'Corporate',
                'nama' => 'PT Nusantara Freight Services',
                'pic_nama' => 'Bambang Sutrisno',
                'pic_phone' => '021-5551239',
                'id_subdistrict' => $subdistrictIds[1] ?? '3171011002',
                'alamat' => 'Jl. Srengseng Sawah No. 78, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST009',
                'type' => 'Individual',
                'nama' => 'Lestari Wulandari',
                'pic_nama' => 'Lestari Wulandari',
                'pic_phone' => '081234567892',
                'id_subdistrict' => $subdistrictIds[2] ?? '3171011003',
                'alamat' => 'Jl. Cipedak No. 90, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST010',
                'type' => 'Corporate',
                'nama' => 'PT Global Logistics Indonesia',
                'pic_nama' => 'Agus Salim',
                'pic_phone' => '021-5551240',
                'id_subdistrict' => $subdistrictIds[3] ?? '3171011004',
                'alamat' => 'Jl. Lenteng Agung No. 101, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST011',
                'type' => 'Corporate',
                'nama' => 'CV Berkah Jaya Transport',
                'pic_nama' => 'Fitri Handayani',
                'pic_phone' => '021-5551241',
                'id_subdistrict' => $subdistrictIds[4] ?? '3171011005',
                'alamat' => 'Jl. Tanjung Barat Raya No. 23, Jakarta Selatan',
            ],
            [
                'kode' => 'CUST012',
                'type' => 'Individual',
                'nama' => 'Wahyu Pratama',
                'pic_nama' => 'Wahyu Pratama',
                'pic_phone' => '081234567893',
                'id_subdistrict' => $subdistrictIds[5] ?? '3171011006',
                'alamat' => 'Jl. Ciganjur No. 45, Jakarta Selatan',
            ],
        ];

        foreach ($pelangganData as $pelanggan) {
            Pelanggan::create($pelanggan);
        }
    }
}
