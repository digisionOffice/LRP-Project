<?php

namespace Database\Factories;

use App\Models\Tbbm;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tbbm>
 */
class TbbmFactory extends Factory
{
    protected $model = Tbbm::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'TBBM ' . $this->faker->city,
            'alamat' => $this->faker->address,
            'created_by' => User::factory(),
        ];
    }
}
