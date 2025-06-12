<?php

namespace Database\Factories;

use App\Models\UangJalan;
use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UangJalan>
 */
class UangJalanFactory extends Factory
{
    protected $model = UangJalan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_do' => DeliveryOrder::factory(),
            'nominal' => $this->faker->numberBetween(100000, 1000000),
            'status_kirim' => $this->faker->randomElement(['pending', 'kirim', 'ditolak']),
            'status_terima' => $this->faker->randomElement(['pending', 'terima', 'ditolak']),
            'id_user' => User::factory(),
            'created_by' => User::factory(),
        ];
    }
}
