<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => 'ITEM-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->randomElement(['Premium', 'Pertamax', 'Solar', 'Pertalite', 'Dexlite']),
            'description' => $this->faker->sentence,
            'id_item_jenis' => 1, // Assuming BBM category exists
            'id_satuan' => 1, // Assuming Liter unit exists
            'created_by' => User::factory(),
        ];
    }
}
