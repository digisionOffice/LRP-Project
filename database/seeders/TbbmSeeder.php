<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tbbm;
use App\Models\Subdistrict;

class TbbmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing subdistrict IDs
        $subdistrictIds = Subdistrict::pluck('id')->toArray();

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
}
