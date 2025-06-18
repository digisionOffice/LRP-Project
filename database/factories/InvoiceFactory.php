<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(5000000, 50000000);
        $taxRate = 11; // 11% PPN
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;
        $totalPaid = $this->faker->numberBetween(0, $totalAmount);

        return [
            'nomor_invoice' => 'INV-' . $this->faker->unique()->numberBetween(100000, 999999),
            'id_do' => DeliveryOrder::factory(),
            'id_transaksi' => TransaksiPenjualan::factory(),
            'tanggal_invoice' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'tanggal_jatuh_tempo' => $this->faker->dateTimeBetween('now', '+60 days'),
            'nama_pelanggan' => $this->faker->company,
            'alamat_pelanggan' => $this->faker->address,
            'npwp_pelanggan' => $this->faker->numerify('##.###.###.#-###.###'),
            'subtotal' => $subtotal,
            'total_pajak' => $taxAmount,
            'total_invoice' => $totalAmount,
            'total_terbayar' => $totalPaid,
            'sisa_tagihan' => $totalAmount - $totalPaid,
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'overdue', 'cancelled']),
            'catatan' => $this->faker->optional()->sentence,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'total_terbayar' => $attributes['total_invoice'],
                'sisa_tagihan' => 0,
            ];
        });
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'overdue',
                'tanggal_jatuh_tempo' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            ];
        });
    }
}
