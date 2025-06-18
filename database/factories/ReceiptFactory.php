<?php

namespace Database\Factories;

use App\Models\Receipt;
use App\Models\Invoice;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receipt>
 */
class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentAmount = $this->faker->numberBetween(1000000, 10000000);
        $adminFee = $paymentAmount * 0.001; // 0.1% admin fee

        return [
            'nomor_receipt' => 'RCP-' . $this->faker->unique()->numberBetween(100000, 999999),
            'id_invoice' => Invoice::factory(),
            'id_do' => DeliveryOrder::factory(),
            'id_transaksi' => TransaksiPenjualan::factory(),
            'tanggal_receipt' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'tanggal_pembayaran' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'metode_pembayaran' => $this->faker->randomElement(['cash', 'transfer', 'check', 'giro']),
            'referensi_pembayaran' => 'REF-' . strtoupper($this->faker->bothify('??##??##')),
            'jumlah_pembayaran' => $paymentAmount,
            'biaya_admin' => $adminFee,
            'total_diterima' => $paymentAmount - $adminFee,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'catatan' => $this->faker->optional()->sentence,
            'bank_pengirim' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB']),
            'bank_penerima' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the receipt is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
            ];
        });
    }

    /**
     * Indicate that the receipt is for cash payment.
     */
    public function cash(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'metode_pembayaran' => 'cash',
                'referensi_pembayaran' => null,
                'bank_pengirim' => null,
                'biaya_admin' => 0,
                'total_diterima' => $attributes['jumlah_pembayaran'],
            ];
        });
    }
}
