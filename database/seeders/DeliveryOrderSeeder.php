<?php

namespace Database\Seeders;

use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryOrderSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $transaksiPenjualan = TransaksiPenjualan::all();
        $users = User::all();
        $kendaraans = DB::table('kendaraans')->get();
        
        if ($transaksiPenjualan->isEmpty() || $users->isEmpty() || $kendaraans->isEmpty()) {
            $this->command->warn('Missing required data. Please run TransaksiPenjualanSeeder, UserSeeder, and KendaraanSeeder first.');
            return;
        }

        $paymentStatuses = ['pending', 'partial', 'paid', 'overdue'];
        $statusMuat = ['pending', 'muat', 'selesai'];
        
        $deliveryOrders = [];
        $createdCount = 0;

        foreach ($transaksiPenjualan as $transaksi) {
            // Create 1-3 delivery orders per transaction
            $deliveryCount = rand(1, 3);
            
            for ($i = 0; $i < $deliveryCount; $i++) {
                $user = $users->random();
                $kendaraan = $kendaraans->random();
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
                $statusMuatValue = $statusMuat[array_rand($statusMuat)];
                
                // Generate delivery date (within last 3 months)
                $deliveryDate = now()->subDays(rand(1, 90));
                
                // Generate loading times based on status
                $waktuMuat = null;
                $waktuSelesaiMuat = null;
                
                if ($statusMuatValue === 'muat' || $statusMuatValue === 'selesai') {
                    $waktuMuat = $deliveryDate->copy()->addHours(rand(1, 6));
                    
                    if ($statusMuatValue === 'selesai') {
                        $waktuSelesaiMuat = $waktuMuat->copy()->addHours(rand(1, 4));
                    }
                }
                
                $deliveryOrders[] = [
                    'kode' => 'DO-' . $deliveryDate->format('Ymd') . '-' . str_pad($createdCount + 1, 4, '0', STR_PAD_LEFT),
                    'id_transaksi' => $transaksi->id,
                    'id_user' => $user->id,
                    'id_kendaraan' => $kendaraan->id,
                    'tanggal_delivery' => $deliveryDate,
                    'no_segel' => 'SGL-' . rand(100000, 999999),
                    'do_signatory_name' => $user->name,
                    'do_print_status' => rand(0, 1),
                    'fuel_usage_notes' => 'Fuel delivery completed successfully. Volume: ' . rand(1000, 5000) . ' liters.',
                    'driver_allowance_amount' => rand(100000, 500000),
                    'allowance_receipt_status' => rand(0, 1),
                    'allowance_receipt_time' => $statusMuatValue === 'selesai' ? $waktuSelesaiMuat : null,
                    'do_handover_status' => $statusMuatValue === 'selesai' ? 1 : 0,
                    'do_handover_time' => $statusMuatValue === 'selesai' ? $waktuSelesaiMuat : null,
                    'invoice_number' => 'INV-' . $deliveryDate->format('Ymd') . '-' . str_pad($createdCount + 1, 4, '0', STR_PAD_LEFT),
                    'tax_invoice_number' => 'TAX-' . $deliveryDate->format('Ymd') . '-' . str_pad($createdCount + 1, 4, '0', STR_PAD_LEFT),
                    'invoice_delivery_status' => rand(0, 1),
                    'invoice_archive_status' => rand(0, 1),
                    'invoice_confirmation_status' => $paymentStatus === 'paid' ? 1 : 0,
                    'invoice_confirmation_time' => $paymentStatus === 'paid' ? $deliveryDate->copy()->addDays(rand(1, 7)) : null,
                    'payment_status' => $paymentStatus,
                    'status_muat' => $statusMuatValue,
                    'waktu_muat' => $waktuMuat,
                    'waktu_selesai_muat' => $waktuSelesaiMuat,
                    'created_by' => $user->id,
                    'created_at' => $deliveryDate,
                    'updated_at' => $deliveryDate,
                ];
                
                $createdCount++;
                
                // Insert in batches of 50
                if (count($deliveryOrders) >= 50) {
                    DB::table('delivery_order')->insert($deliveryOrders);
                    $deliveryOrders = [];
                }
            }
        }
        
        // Insert remaining records
        if (!empty($deliveryOrders)) {
            DB::table('delivery_order')->insert($deliveryOrders);
        }

        $this->command->info("DeliveryOrder seeder completed! Created {$createdCount} delivery orders.");
        
        // Display summary
        $this->command->info("Summary:");
        $this->command->info("- Pending: " . DB::table('delivery_order')->where('payment_status', 'pending')->count());
        $this->command->info("- Partial: " . DB::table('delivery_order')->where('payment_status', 'partial')->count());
        $this->command->info("- Paid: " . DB::table('delivery_order')->where('payment_status', 'paid')->count());
        $this->command->info("- Overdue: " . DB::table('delivery_order')->where('payment_status', 'overdue')->count());
        $this->command->info("- Load Pending: " . DB::table('delivery_order')->where('status_muat', 'pending')->count());
        $this->command->info("- Loading: " . DB::table('delivery_order')->where('status_muat', 'muat')->count());
        $this->command->info("- Completed: " . DB::table('delivery_order')->where('status_muat', 'selesai')->count());
    }
}
