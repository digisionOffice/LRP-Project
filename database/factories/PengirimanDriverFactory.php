<?php

namespace Database\Factories;

use App\Models\PengirimanDriver;
use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PengirimanDriver>
 */
class PengirimanDriverFactory extends Factory
{
    protected $model = PengirimanDriver::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('-7 days', 'now');
        $departureTime = $this->faker->dateTimeBetween($startTime, $startTime->format('Y-m-d H:i:s') . ' +2 hours');
        $arrivalTime = $this->faker->dateTimeBetween($departureTime, $departureTime->format('Y-m-d H:i:s') . ' +6 hours');
        $completionTime = $this->faker->dateTimeBetween($arrivalTime, $arrivalTime->format('Y-m-d H:i:s') . ' +2 hours');

        return [
            'id_do' => DeliveryOrder::factory(),
            'totalisator_awal' => $this->faker->numberBetween(10000, 50000),
            'totalisator_tiba' => $this->faker->numberBetween(15000, 55000),
            'waktu_mulai' => $startTime,
            'waktu_berangkat' => $departureTime,
            'waktu_tiba' => $arrivalTime,
            'waktu_selesai' => $completionTime,
            'volume_terkirim' => $this->faker->numberBetween(500, 5000),
            'totalisator_pool_return' => $this->faker->numberBetween(20000, 60000),
            'waktu_pool_arrival' => $this->faker->dateTimeBetween($completionTime, $completionTime->format('Y-m-d H:i:s') . ' +4 hours'),
            'created_by' => User::factory(),
        ];
    }
}
