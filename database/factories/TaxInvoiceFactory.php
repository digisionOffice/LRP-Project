<?php

namespace Database\Factories;

use App\Models\TaxInvoice;
use App\Models\Invoice;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxInvoice>
 */
class TaxInvoiceFactory extends Factory
{
    protected $model = TaxInvoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dpp = $this->faker->numberBetween(5000000, 50000000);
        $taxRate = 11; // 11% PPN
        $ppn = $dpp * ($taxRate / 100);
        $total = $dpp + $ppn;

        return [
            'nomor_tax_invoice' => 'FP-' . $this->faker->unique()->numberBetween(100000, 999999),
            'id_invoice' => Invoice::factory(),
            'id_do' => DeliveryOrder::factory(),
            'id_transaksi' => TransaksiPenjualan::factory(),
            'tanggal_tax_invoice' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'nama_pelanggan' => $this->faker->company,
            'alamat_pelanggan' => $this->faker->address,
            'npwp_pelanggan' => $this->faker->numerify('##.###.###.#-###.###'),
            'nama_perusahaan' => 'PT. Logistik Riau Prima',
            'alamat_perusahaan' => 'Jl. Riau Prima No. 123, Pekanbaru',
            'npwp_perusahaan' => '01.234.567.8-901.000',
            'dasar_pengenaan_pajak' => $dpp,
            'tarif_pajak' => $taxRate,
            'pajak_pertambahan_nilai' => $ppn,
            'total_tax_invoice' => $total,
            'status' => $this->faker->randomElement(['draft', 'submitted', 'approved', 'rejected']),
            'catatan' => $this->faker->optional()->sentence,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the tax invoice is approved.
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    /**
     * Indicate that the tax invoice is rejected.
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
            ];
        });
    }
}
