<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AlamatPelanggan>
 */
class AlamatPelangganFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pelanggan' => \App\Models\Pelanggan::factory(),
            'alamat' => $this->faker->address(),
            'latitude' => $this->faker->latitude(-8.0, -5.0), // Indonesia latitude range
            'longitude' => $this->faker->longitude(95.0, 141.0), // Indonesia longitude range
            'is_primary' => $this->faker->boolean(30), // 30% chance of being primary
        ];
    }

    /**
     * Indicate that the address is primary.
     */
    public function primary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Indicate that the address has no coordinates.
     */
    public function withoutCoordinates(): static
    {
        return $this->state(fn(array $attributes) => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    /**
     * Set specific coordinates (Jakarta area).
     */
    public function jakarta(): static
    {
        return $this->state(fn(array $attributes) => [
            'latitude' => $this->faker->latitude(-6.3, -6.1),
            'longitude' => $this->faker->longitude(106.7, 106.9),
        ]);
    }
}
