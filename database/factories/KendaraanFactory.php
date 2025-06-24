<?php

namespace Database\Factories;

use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kendaraan>
 */
class KendaraanFactory extends Factory
{
    protected $model = Kendaraan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_polisi' => $this->faker->unique()->regexify('[A-Z]{1}[0-9]{4}[A-Z]{3}'),
            'merk' => $this->faker->randomElement(['Hino', 'Mitsubishi', 'Isuzu', 'Mercedes']),
            'tipe' => $this->faker->randomElement(['Ranger', 'Canter', 'Elf', 'Actros']),
            'kapasitas' => $this->faker->numberBetween(5000, 15000),
            'kapasitas_satuan' => 1, // Assuming Liter unit ID is 1
            'tanggal_awal_valid' => now(),
            'tanggal_akhir_valid' => now()->addYear(),
            'deskripsi' => $this->faker->sentence,
            'created_by' => User::factory(),
        ];
    }
}
