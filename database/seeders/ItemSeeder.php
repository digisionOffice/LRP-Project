<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemKategori;
use App\Models\SatuanDasar;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing kategori and satuan IDs
        $kategoriIds = ItemKategori::pluck('id')->toArray();
        $satuanIds = SatuanDasar::pluck('id')->toArray();

        $itemData = [
            [
                'kode' => 'BBM001',
                'name' => 'Solar Industri',
                'description' => 'Solar untuk keperluan industri dan transportasi',
                'id_item_jenis' => $kategoriIds[1] ?? 2, // Solar
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
            [
                'kode' => 'BBM002',
                'name' => 'Pertamax',
                'description' => 'Bahan bakar premium dengan oktan tinggi',
                'id_item_jenis' => $kategoriIds[2] ?? 3, // Pertamax
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
            [
                'kode' => 'BBM003',
                'name' => 'Premium',
                'description' => 'Bahan bakar bensin standar',
                'id_item_jenis' => $kategoriIds[3] ?? 4, // Premium
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
            [
                'kode' => 'BBM004',
                'name' => 'Pertalite',
                'description' => 'Bahan bakar bensin dengan oktan 90',
                'id_item_jenis' => $kategoriIds[4] ?? 5, // Pertalite
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
            [
                'kode' => 'BBM005',
                'name' => 'Pertamax Turbo',
                'description' => 'Bahan bakar premium dengan oktan 98',
                'id_item_jenis' => $kategoriIds[2] ?? 3, // Pertamax
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
            [
                'kode' => 'BBM006',
                'name' => 'Dexlite',
                'description' => 'Solar dengan kualitas tinggi untuk kendaraan ringan',
                'id_item_jenis' => $kategoriIds[1] ?? 2, // Solar
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
            [
                'kode' => 'BBM007',
                'name' => 'Pertamina Dex',
                'description' => 'Solar premium untuk kendaraan diesel',
                'id_item_jenis' => $kategoriIds[1] ?? 2, // Solar
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
            [
                'kode' => 'BBM008',
                'name' => 'Avtur',
                'description' => 'Bahan bakar pesawat terbang',
                'id_item_jenis' => $kategoriIds[0] ?? 1, // BBM
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
            [
                'kode' => 'BBM009',
                'name' => 'Avgas',
                'description' => 'Bahan bakar pesawat kecil',
                'id_item_jenis' => $kategoriIds[0] ?? 1, // BBM
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
            [
                'kode' => 'BBM010',
                'name' => 'Minyak Tanah',
                'description' => 'Minyak tanah untuk keperluan rumah tangga',
                'id_item_jenis' => $kategoriIds[0] ?? 1, // BBM
                'id_satuan' => $satuanIds[0] ?? 1, // Liter
            ],
        ];

        foreach ($itemData as $item) {
            Item::firstOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
