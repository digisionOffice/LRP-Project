<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\TaxInvoice;
use App\Models\User;

class FinancialSeeder extends Seeder
{
    /**
     * Run the financial management seeder.
     */
    public function run(): void
    {
        $this->command->info('Seeding financial management data...');

        // Get existing data
        $deliveryOrders = DeliveryOrder::with(['transaksi.pelanggan', 'transaksi.penjualanDetails'])->get();
        $users = User::all();

        if ($deliveryOrders->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Missing required data for financial management. Skipping.');
            return;
        }

        $adminUser = $users->where('email', 'superadmin@lrp.com')->first() ?? $users->first();

        foreach ($deliveryOrders as $deliveryOrder) {
            if (!$deliveryOrder->transaksi || !$deliveryOrder->transaksi->pelanggan) {
                continue;
            }

            $transaksi = $deliveryOrder->transaksi;
            $pelanggan = $transaksi->pelanggan;

            // Calculate amounts from sales details
            $subtotal = 0;
            if ($transaksi->penjualanDetails && $transaksi->penjualanDetails->count() > 0) {
                $subtotal = $transaksi->penjualanDetails->sum(function ($detail) {
                    return $detail->volume_item * $detail->harga_jual;
                });
            }

            if ($subtotal <= 0) {
                $subtotal = rand(5000000, 50000000); // Fallback random amount
            }

            $taxRate = 11; // 11% PPN
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Create Invoice
            $invoice = Invoice::create([
                'nomor_invoice' => 'INV-' . str_pad($deliveryOrder->id, 6, '0', STR_PAD_LEFT),
                'id_do' => $deliveryOrder->id,
                'id_transaksi' => $transaksi->id,
                'tanggal_invoice' => $deliveryOrder->tanggal_delivery ?? now()->subDays(rand(1, 30)),
                'tanggal_jatuh_tempo' => ($deliveryOrder->tanggal_delivery ?? now())->addDays(30),
                'nama_pelanggan' => $pelanggan->nama,
                'alamat_pelanggan' => $pelanggan->alamat,
                'npwp_pelanggan' => $pelanggan->npwp,
                'subtotal' => $subtotal,
                'total_pajak' => $taxAmount,
                'total_invoice' => $totalAmount,
                'total_terbayar' => 0,
                'sisa_tagihan' => $totalAmount,
                'status' => ['draft', 'sent', 'paid', 'overdue'][rand(0, 3)],
                'catatan' => 'Invoice untuk delivery order ' . $deliveryOrder->kode,
                'created_by' => $adminUser->id,
            ]);

            // Create Tax Invoice (for some invoices)
            if (rand(0, 1)) {
                TaxInvoice::create([
                    'nomor_tax_invoice' => 'FP-' . str_pad($deliveryOrder->id, 6, '0', STR_PAD_LEFT),
                    'id_invoice' => $invoice->id,
                    'id_do' => $deliveryOrder->id,
                    'id_transaksi' => $transaksi->id,
                    'tanggal_tax_invoice' => $invoice->tanggal_invoice,
                    'nama_pelanggan' => $pelanggan->nama,
                    'alamat_pelanggan' => $pelanggan->alamat,
                    'npwp_pelanggan' => $pelanggan->npwp,
                    'nama_perusahaan' => 'PT. Logistik Riau Prima',
                    'alamat_perusahaan' => 'Jl. Riau Prima No. 123, Pekanbaru',
                    'npwp_perusahaan' => '01.234.567.8-901.000',
                    'dasar_pengenaan_pajak' => $subtotal,
                    'tarif_pajak' => $taxRate,
                    'pajak_pertambahan_nilai' => $taxAmount,
                    'total_tax_invoice' => $totalAmount,
                    'status' => ['draft', 'submitted', 'approved'][rand(0, 2)],
                    'catatan' => 'Faktur pajak untuk invoice ' . $invoice->nomor_invoice,
                    'created_by' => $adminUser->id,
                ]);
            }

            // Create Receipts (for paid/partially paid invoices)
            if (in_array($invoice->status, ['paid']) || rand(0, 2) == 0) {
                $paymentMethods = ['transfer', 'cash', 'check', 'giro'];
                $numPayments = $invoice->status === 'paid' ? rand(1, 3) : rand(1, 2);
                $totalPaid = 0;

                for ($i = 0; $i < $numPayments; $i++) {
                    $remainingAmount = $totalAmount - $totalPaid;
                    if ($remainingAmount <= 0) break;

                    $paymentAmount = $i === $numPayments - 1 && $invoice->status === 'paid'
                        ? $remainingAmount
                        : rand(1000000, min($remainingAmount, $totalAmount * 0.7));

                    $adminFee = $paymentAmount * 0.001; // 0.1% admin fee

                    Receipt::create([
                        'nomor_receipt' => 'RCP-' . str_pad($deliveryOrder->id, 6, '0', STR_PAD_LEFT) . '-' . ($i + 1),
                        'id_invoice' => $invoice->id,
                        'id_do' => $deliveryOrder->id,
                        'id_transaksi' => $transaksi->id,
                        'tanggal_receipt' => $invoice->tanggal_invoice->addDays(rand(1, 15)),
                        'tanggal_pembayaran' => $invoice->tanggal_invoice->addDays(rand(1, 15)),
                        'metode_pembayaran' => $paymentMethods[array_rand($paymentMethods)],
                        'referensi_pembayaran' => 'REF-' . strtoupper(uniqid()),
                        'jumlah_pembayaran' => $paymentAmount,
                        'biaya_admin' => $adminFee,
                        'total_diterima' => $paymentAmount - $adminFee,
                        'status' => ['pending', 'confirmed'][rand(0, 1)],
                        'catatan' => 'Pembayaran untuk invoice ' . $invoice->nomor_invoice,
                        'bank_pengirim' => ['BCA', 'Mandiri', 'BNI', 'BRI'][rand(0, 3)],
                        'bank_penerima' => 'BCA',
                        'created_by' => $adminUser->id,
                    ]);

                    $totalPaid += $paymentAmount;
                }

                // Update invoice payment status
                $invoice->update([
                    'total_terbayar' => $totalPaid,
                    'sisa_tagihan' => $totalAmount - $totalPaid,
                    'status' => $totalPaid >= $totalAmount ? 'paid' : 'sent'
                ]);
            }
        }

        $this->command->info('Financial management data seeded successfully!');
    }
}
