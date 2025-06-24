<?php

namespace Database\Factories;

use App\Models\TransaksiPenjualan;
use App\Models\Pelanggan;
use App\Models\Tbbm;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransaksiPenjualan>
 */
class TransaksiPenjualanFactory extends Factory
{
    protected $model = TransaksiPenjualan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => 'SO-' . $this->faker->unique()->numberBetween(100000, 999999),
            'tipe' => $this->faker->randomElement(['dagang', 'jasa']),
            'tanggal' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'id_pelanggan' => Pelanggan::factory(),
            'alamat' => $this->faker->address,
            'nomor_po' => 'PO-' . $this->faker->unique()->numberBetween(100000, 999999),
            'top_pembayaran' => $this->faker->numberBetween(0, 60),
            'id_tbbm' => Tbbm::factory(),
            'created_by' => User::factory(),
        ];
    }
}
