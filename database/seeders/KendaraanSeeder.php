<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kendaraan;
use Carbon\Carbon;

class KendaraanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            [
                'no_pol_kendaraan' => 'B 3456 JKL',
                'merk' => 'Hino',
                'tipe' => 'Ranger FM 350 PD',
                'kapasitas' => 18000.0, // 18,000 liters
                'kapasitas_satuan' => 1,
                'tanggal_awal_valid' => Carbon::create(2023, 8, 10),
                'tanggal_akhir_valid' => Carbon::create(2026, 8, 9),
                'deskripsi' => 'Truk tangki BBM kapasitas 18KL, kondisi sangat baik, untuk distribusi pertalite',
            ],
            [
                'no_pol_kendaraan' => 'B 7890 MNO',
                'merk' => 'Mitsubishi Fuso',
                'tipe' => 'Fighter FK 417',
                'kapasitas' => 8000.0, // 8,000 liters
                'kapasitas_satuan' => 1,
                'tanggal_awal_valid' => Carbon::create(2023, 2, 20),
                'tanggal_akhir_valid' => Carbon::create(2025, 2, 19),
                'deskripsi' => 'Truk tangki BBM kapasitas 8KL, kondisi baik, untuk distribusi area terbatas',
            ],
            [
                'no_pol_kendaraan' => 'B 2468 PQR',
                'merk' => 'Isuzu',
                'tipe' => 'Elf NMR 71',
                'kapasitas' => 5000.0, // 5,000 liters
                'kapasitas_satuan' => 1,
                'tanggal_awal_valid' => Carbon::create(2023, 5, 5),
                'tanggal_akhir_valid' => Carbon::create(2025, 5, 4),
                'deskripsi' => 'Truk tangki BBM kapasitas 5KL, kondisi baik, untuk distribusi retail',
            ],
            [
                'no_pol_kendaraan' => 'B 1357 STU',
                'merk' => 'Hino',
                'tipe' => 'Dutro 130 HD',
                'kapasitas' => 6000.0, // 6,000 liters
                'kapasitas_satuan' => 1,
                'tanggal_awal_valid' => Carbon::create(2023, 7, 12),
                'tanggal_akhir_valid' => Carbon::create(2025, 7, 11),
                'deskripsi' => 'Truk tangki BBM kapasitas 6KL, kondisi sangat baik, untuk distribusi SPBU kecil',
            ],
            [
                'no_pol_kendaraan' => 'B 9753 VWX',
                'merk' => 'Mitsubishi Fuso',
                'tipe' => 'Canter FE 84G',
                'kapasitas' => 4000.0, // 4,000 liters
                'kapasitas_satuan' => 1,
                'tanggal_awal_valid' => Carbon::create(2023, 4, 18),
                'tanggal_akhir_valid' => Carbon::create(2025, 4, 17),
                'deskripsi' => 'Truk tangki BBM kapasitas 4KL, kondisi baik, untuk distribusi emergency',
            ],
            [
                'no_pol_kendaraan' => 'B 8642 YZA',
                'merk' => 'Isuzu',
                'tipe' => 'Giga FVR 900',
                'kapasitas' => 24000.0, // 24,000 liters
                'kapasitas_satuan' => 1,
                'tanggal_awal_valid' => Carbon::create(2022, 11, 25),
                'tanggal_akhir_valid' => Carbon::create(2025, 11, 24),
                'deskripsi' => 'Truk tangki BBM kapasitas 24KL, kondisi sangat baik, untuk distribusi volume besar',
            ],
            [
                'no_pol_kendaraan' => 'B 1122 BCD',
                'merk' => 'Hino',
                'tipe' => 'Ranger FG 235 JJ',
                'kapasitas' => 14000.0, // 14,000 liters
                'kapasitas_satuan' => 1,
                'tanggal_awal_valid' => Carbon::create(2023, 9, 30),
                'tanggal_akhir_valid' => Carbon::create(2026, 9, 29),
                'deskripsi' => 'Truk tangki BBM kapasitas 14KL, kondisi baik, untuk distribusi regional',
            ],
        ];

        foreach ($kendaraanData as $kendaraan) {
            Kendaraan::create($kendaraan);
        }
    }
}
