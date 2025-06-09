<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MasterDataSeeder::class,
            ItemSeeder::class,
            TbbmSeeder::class,
            KaryawanSeeder::class,
            PelangganSeeder::class,
            SupplierSeeder::class,
            KendaraanSeeder::class,
        ]);
    }
}
