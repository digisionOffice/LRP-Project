<?php

namespace Database\Factories;

use App\Models\PenjualanDetail;
use App\Models\TransaksiPenjualan;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PenjualanDetail>
 */
class PenjualanDetailFactory extends Factory
{
    protected $model = PenjualanDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_transaksi_penjualan' => TransaksiPenjualan::factory(),
            'id_item' => Item::factory(),
            'volume_item' => $this->faker->numberBetween(500, 5000),
            'harga_jual' => $this->faker->numberBetween(10000, 20000),
            'created_by' => User::factory(),
        ];
    }
}
