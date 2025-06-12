<?php

namespace Database\Factories;

use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use App\Models\Kendaraan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryOrder>
 */
class DeliveryOrderFactory extends Factory
{
    protected $model = DeliveryOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => 'DO-' . $this->faker->unique()->numberBetween(100000, 999999),
            'id_transaksi' => TransaksiPenjualan::factory(),
            'id_user' => User::factory(),
            'id_kendaraan' => Kendaraan::factory(),
            'tanggal_delivery' => $this->faker->dateTimeBetween('now', '+7 days'),
            'no_segel' => 'SEAL-' . $this->faker->numberBetween(100000, 999999),
            'status_muat' => $this->faker->randomElement(['pending', 'muat', 'selesai']),
            'created_by' => User::factory(),
        ];
    }
}
