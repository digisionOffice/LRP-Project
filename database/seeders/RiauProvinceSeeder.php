<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\User;

class RiauProvinceSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get or create admin user
        $adminUser = User::where('email', 'superadmin@lrp.com')->first()
            ?? User::where('role', 'super_admin')->first()
            ?? User::first();

        // Create Riau Province
        $riau = Province::firstOrCreate([
            'id' => '14'
        ], [
            'name' => 'Riau',
            'created_by' => $adminUser?->id,
        ]);

        $this->command->info('Creating Riau Province administrative data...');

        // Riau Regencies with their districts and subdistricts
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
                    ['id' => '140103', 'name' => 'Kuantan Hilir', 'subdistricts' => [
                        ['id' => '1401030001', 'name' => 'Baserah'],
                        ['id' => '1401030002', 'name' => 'Cerenti'],
                        ['id' => '1401030003', 'name' => 'Pulau Muda'],
                    ]],
                ]
            ],
            [
                'id' => '1402',
                'name' => 'Kabupaten Indragiri Hulu',
                'districts' => [
                    ['id' => '140201', 'name' => 'Rengat', 'subdistricts' => [
                        ['id' => '1402010001', 'name' => 'Rengat'],
                        ['id' => '1402010002', 'name' => 'Sail'],
                        ['id' => '1402010003', 'name' => 'Kampung Baru'],
                    ]],
                    ['id' => '140202', 'name' => 'Rengat Barat', 'subdistricts' => [
                        ['id' => '1402020001', 'name' => 'Rengat Barat'],
                        ['id' => '1402020002', 'name' => 'Sungai Lala'],
                        ['id' => '1402020003', 'name' => 'Pasir Penyu'],
                    ]],
                    ['id' => '140203', 'name' => 'Lirik', 'subdistricts' => [
                        ['id' => '1402030001', 'name' => 'Lirik'],
                        ['id' => '1402030002', 'name' => 'Sungai Gergaji'],
                        ['id' => '1402030003', 'name' => 'Lubuk Terap'],
                    ]],
                ]
            ],
            [
                'id' => '1403',
                'name' => 'Kabupaten Indragiri Hilir',
                'districts' => [
                    ['id' => '140301', 'name' => 'Tembilahan', 'subdistricts' => [
                        ['id' => '1403010001', 'name' => 'Tembilahan Kota'],
                        ['id' => '1403010002', 'name' => 'Tembilahan Hulu'],
                        ['id' => '1403010003', 'name' => 'Sungai Beringin'],
                    ]],
                    ['id' => '140302', 'name' => 'Tembilahan Hulu', 'subdistricts' => [
                        ['id' => '1403020001', 'name' => 'Tembilahan Hulu'],
                        ['id' => '1403020002', 'name' => 'Sungai Luar'],
                        ['id' => '1403020003', 'name' => 'Pekan Kamis'],
                    ]],
                    ['id' => '140303', 'name' => 'Keritang', 'subdistricts' => [
                        ['id' => '1403030001', 'name' => 'Keritang'],
                        ['id' => '1403030002', 'name' => 'Sungai Batang'],
                        ['id' => '1403030003', 'name' => 'Teluk Belitung'],
                    ]],
                ]
            ],
            [
                'id' => '1404',
                'name' => 'Kabupaten Pelalawan',
                'districts' => [
                    ['id' => '140401', 'name' => 'Pangkalan Kerinci', 'subdistricts' => [
                        ['id' => '1404010001', 'name' => 'Pangkalan Kerinci'],
                        ['id' => '1404010002', 'name' => 'Sungai Tohor'],
                        ['id' => '1404010003', 'name' => 'Kerinci Kanan'],
                    ]],
                    ['id' => '140402', 'name' => 'Pangkalan Lesung', 'subdistricts' => [
                        ['id' => '1404020001', 'name' => 'Pangkalan Lesung'],
                        ['id' => '1404020002', 'name' => 'Sungai Limau'],
                        ['id' => '1404020003', 'name' => 'Teluk Meranti'],
                    ]],
                    ['id' => '140403', 'name' => 'Langgam', 'subdistricts' => [
                        ['id' => '1404030001', 'name' => 'Langgam'],
                        ['id' => '1404030002', 'name' => 'Sungai Pagar'],
                        ['id' => '1404030003', 'name' => 'Perawang'],
                    ]],
                ]
            ],
            [
                'id' => '1405',
                'name' => 'Kabupaten Siak',
                'districts' => [
                    ['id' => '140501', 'name' => 'Siak', 'subdistricts' => [
                        ['id' => '1405010001', 'name' => 'Siak'],
                        ['id' => '1405010002', 'name' => 'Kampung Rempak'],
                        ['id' => '1405010003', 'name' => 'Suak Lanjut'],
                    ]],
                    ['id' => '140502', 'name' => 'Kandis', 'subdistricts' => [
                        ['id' => '1405020001', 'name' => 'Kandis'],
                        ['id' => '1405020002', 'name' => 'Sungai Mandau'],
                        ['id' => '1405020003', 'name' => 'Duri Timur'],
                    ]],
                    ['id' => '140503', 'name' => 'Tualang', 'subdistricts' => [
                        ['id' => '1405030001', 'name' => 'Tualang'],
                        ['id' => '1405030002', 'name' => 'Pekan Tualang'],
                        ['id' => '1405030003', 'name' => 'Sungai Selari'],
                    ]],
                ]
            ],
            [
                'id' => '1406',
                'name' => 'Kabupaten Kampar',
                'districts' => [
                    ['id' => '140601', 'name' => 'Kampar', 'subdistricts' => [
                        ['id' => '1406010001', 'name' => 'Kampar'],
                        ['id' => '1406010002', 'name' => 'Kampar Kiri'],
                        ['id' => '1406010003', 'name' => 'Kampar Kiri Hilir'],
                    ]],
                    ['id' => '140602', 'name' => 'Kampar Kiri', 'subdistricts' => [
                        ['id' => '1406020001', 'name' => 'Kampar Kiri'],
                        ['id' => '1406020002', 'name' => 'Salo'],
                        ['id' => '1406020003', 'name' => 'Gunung Sahilan'],
                    ]],
                    ['id' => '140603', 'name' => 'Bangkinang', 'subdistricts' => [
                        ['id' => '1406030001', 'name' => 'Bangkinang'],
                        ['id' => '1406030002', 'name' => 'Bangkinang Kota'],
                        ['id' => '1406030003', 'name' => 'Kuok'],
                    ]],
                    ['id' => '140604', 'name' => 'Bangkinang Barat', 'subdistricts' => [
                        ['id' => '1406040001', 'name' => 'Bangkinang Barat'],
                        ['id' => '1406040002', 'name' => 'Siak Hulu'],
                        ['id' => '1406040003', 'name' => 'Tapung'],
                    ]],
                ]
            ],
            [
                'id' => '1407',
                'name' => 'Kabupaten Rokan Hulu',
                'districts' => [
                    ['id' => '140701', 'name' => 'Pasir Pengaraian', 'subdistricts' => [
                        ['id' => '1407010001', 'name' => 'Pasir Pengaraian'],
                        ['id' => '1407010002', 'name' => 'Ujung Batu'],
                        ['id' => '1407010003', 'name' => 'Rokan IV Koto'],
                    ]],
                    ['id' => '140702', 'name' => 'Tambusai', 'subdistricts' => [
                        ['id' => '1407020001', 'name' => 'Tambusai'],
                        ['id' => '1407020002', 'name' => 'Tambusai Utara'],
                        ['id' => '1407020003', 'name' => 'Kepenuhan'],
                    ]],
                    ['id' => '140703', 'name' => 'Rambah', 'subdistricts' => [
                        ['id' => '1407030001', 'name' => 'Rambah'],
                        ['id' => '1407030002', 'name' => 'Rambah Samo'],
                        ['id' => '1407030003', 'name' => 'Rambah Hilir'],
                    ]],
                ]
            ],
            [
                'id' => '1408',
                'name' => 'Kabupaten Bengkalis',
                'districts' => [
                    ['id' => '140801', 'name' => 'Bengkalis', 'subdistricts' => [
                        ['id' => '1408010001', 'name' => 'Bengkalis'],
                        ['id' => '1408010002', 'name' => 'Senapelan'],
                        ['id' => '1408010003', 'name' => 'Bantan'],
                    ]],
                    ['id' => '140802', 'name' => 'Bukit Batu', 'subdistricts' => [
                        ['id' => '1408020001', 'name' => 'Bukit Batu'],
                        ['id' => '1408020002', 'name' => 'Duri'],
                        ['id' => '1408020003', 'name' => 'Mandau'],
                    ]],
                    ['id' => '140803', 'name' => 'Siak Kecil', 'subdistricts' => [
                        ['id' => '1408030001', 'name' => 'Siak Kecil'],
                        ['id' => '1408030002', 'name' => 'Rupat'],
                        ['id' => '1408030003', 'name' => 'Rupat Utara'],
                    ]],
                ]
            ],
            [
                'id' => '1409',
                'name' => 'Kabupaten Rokan Hilir',
                'districts' => [
                    ['id' => '140901', 'name' => 'Bagansiapiapi', 'subdistricts' => [
                        ['id' => '1409010001', 'name' => 'Bagansiapiapi'],
                        ['id' => '1409010002', 'name' => 'Bangko'],
                        ['id' => '1409010003', 'name' => 'Sinaboi'],
                    ]],
                    ['id' => '140902', 'name' => 'Tanah Putih', 'subdistricts' => [
                        ['id' => '1409020001', 'name' => 'Tanah Putih'],
                        ['id' => '1409020002', 'name' => 'Bagan Sinembah'],
                        ['id' => '1409020003', 'name' => 'Kubu'],
                    ]],
                    ['id' => '140903', 'name' => 'Bagan Sinembah', 'subdistricts' => [
                        ['id' => '1409030001', 'name' => 'Bagan Sinembah'],
                        ['id' => '1409030002', 'name' => 'Pasir Limau Kapas'],
                        ['id' => '1409030003', 'name' => 'Rimba Melintang'],
                    ]],
                ]
            ],
            [
                'id' => '1410',
                'name' => 'Kabupaten Kepulauan Meranti',
                'districts' => [
                    ['id' => '141001', 'name' => 'Tebing Tinggi', 'subdistricts' => [
                        ['id' => '1410010001', 'name' => 'Tebing Tinggi'],
                        ['id' => '1410010002', 'name' => 'Tebing Tinggi Barat'],
                        ['id' => '1410010003', 'name' => 'Tebing Tinggi Timur'],
                    ]],
                    ['id' => '141002', 'name' => 'Rangsang', 'subdistricts' => [
                        ['id' => '1410020001', 'name' => 'Rangsang'],
                        ['id' => '1410020002', 'name' => 'Rangsang Barat'],
                        ['id' => '1410020003', 'name' => 'Rangsang Pesisir'],
                    ]],
                    ['id' => '141003', 'name' => 'Merbau', 'subdistricts' => [
                        ['id' => '1410030001', 'name' => 'Merbau'],
                        ['id' => '1410030002', 'name' => 'Pulau Merbau'],
                        ['id' => '1410030003', 'name' => 'Tasik Putri Puyu'],
                    ]],
                ]
            ],
            // Cities (Kota)
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
                    ['id' => '147103', 'name' => 'Sail', 'subdistricts' => [
                        ['id' => '1471030001', 'name' => 'Sail'],
                        ['id' => '1471030002', 'name' => 'Suka Maju'],
                        ['id' => '1471030003', 'name' => 'Cinta Raja'],
                    ]],
                    ['id' => '147104', 'name' => 'Pekanbaru Kota', 'subdistricts' => [
                        ['id' => '1471040001', 'name' => 'Kota Tinggi'],
                        ['id' => '1471040002', 'name' => 'Kota Baru'],
                        ['id' => '1471040003', 'name' => 'Sumahilang'],
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
                    ['id' => '147302', 'name' => 'Dumai Timur', 'subdistricts' => [
                        ['id' => '1473020001', 'name' => 'Dumai Timur'],
                        ['id' => '1473020002', 'name' => 'Buluh Kasap'],
                        ['id' => '1473020003', 'name' => 'Tanjung Palas'],
                    ]],
                    ['id' => '147303', 'name' => 'Dumai Kota', 'subdistricts' => [
                        ['id' => '1473030001', 'name' => 'Dumai Kota'],
                        ['id' => '1473030002', 'name' => 'Pangkalan Sesai'],
                        ['id' => '1473030003', 'name' => 'Teluk Binjai'],
                    ]],
                ]
            ],
        ];

        // Create regencies, districts, and subdistricts
        foreach ($regenciesData as $regencyData) {
            $regency = Regency::firstOrCreate([
                'id' => $regencyData['id']
            ], [
                'province_id' => $riau->id,
                'name' => $regencyData['name'],
                'created_by' => $adminUser?->id,
            ]);

            $this->command->info("Created regency: {$regency->name}");

            foreach ($regencyData['districts'] as $districtData) {
                $district = District::firstOrCreate([
                    'id' => $districtData['id']
                ], [
                    'regency_id' => $regency->id,
                    'name' => $districtData['name'],
                    'created_by' => $adminUser?->id,
                ]);

                $this->command->info("  Created district: {$district->name}");

                foreach ($districtData['subdistricts'] as $subdistrictData) {
                    $subdistrict = Subdistrict::firstOrCreate([
                        'id' => $subdistrictData['id']
                    ], [
                        'district_id' => $district->id,
                        'name' => $subdistrictData['name'],
                        'created_by' => $adminUser?->id,
                    ]);

                    $this->command->info("    Created subdistrict: {$subdistrict->name}");
                }
            }
        }

        $this->command->info('Riau Province administrative data created successfully!');
        $this->command->info("Total created:");
        $this->command->info("- Regencies: " . $riau->regencies()->count());
        $this->command->info("- Districts: " . $riau->districts()->count());
        $this->command->info("- Subdistricts: " . $riau->subdistricts()->count());
    }
}
