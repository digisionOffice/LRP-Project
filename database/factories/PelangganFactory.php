<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggan>
 */
class PelangganFactory extends Factory
{
    protected $model = Pelanggan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => 'CUST-' . $this->faker->unique()->numberBetween(1000, 9999),
            'type' => $this->faker->randomElement(['individual', 'corporate']),
            'nama' => $this->faker->company,
            'pic_nama' => $this->faker->name,
            'pic_phone' => $this->faker->phoneNumber,
            'alamat' => $this->faker->address,
            'created_by' => User::factory(),
        ];
    }
}
