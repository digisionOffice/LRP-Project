<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransaksiPenjualan;
use App\Models\PenjualanDetail;
use App\Models\DeliveryOrder;
use App\Models\PengirimanDriver;
use App\Models\UangJalan;
use App\Models\Pelanggan;
use App\Models\Item;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Tbbm;
use App\Models\User;

class FuelDeliveryTestSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get or create test user
        $user = User::firstOrCreate([
            'email' => 'admin@test.com'
        ], [
            'name' => 'Admin Test',
            'password' => bcrypt('password'),
        ]);

        // Create test customers if they don't exist
        $customers = [];
        for ($i = 1; $i <= 5; $i++) {
            $customers[] = Pelanggan::firstOrCreate([
                'kode' => "CUST-{$i}"
            ], [
                'type' => 'corporate',
                'nama' => "Customer {$i}",
                'pic_nama' => "PIC Customer {$i}",
                'pic_phone' => "08123456789{$i}",
                'alamat' => "Address Customer {$i}",
                'created_by' => $user->id,
            ]);
        }

        // Create test fuel items if they don't exist
        $fuelItems = [];
        $fuelTypes = ['Premium', 'Pertamax', 'Solar', 'Pertalite', 'Dexlite'];
        foreach ($fuelTypes as $index => $fuelType) {
            $fuelItems[] = Item::firstOrCreate([
                'kode' => "FUEL-{$index}"
            ], [
                'name' => $fuelType,
                'description' => "Fuel type {$fuelType}",
                'id_item_jenis' => 1, // Assuming BBM category exists
                'id_satuan' => 1, // Assuming Liter unit exists
                'created_by' => $user->id,
            ]);
        }

        // Create test drivers if they don't exist
        $drivers = [];
        for ($i = 1; $i <= 3; $i++) {
            $drivers[] = Karyawan::firstOrCreate([
                'no_induk' => "DRV-{$i}"
            ], [
                'nama' => "Driver {$i}",
                'hp' => "08123456789{$i}",
                'email' => "driver{$i}@test.com",
                'id_jabatan' => 1, // Assuming driver position exists
                'id_divisi' => 1, // Assuming operations division exists
                'created_by' => $user->id,
            ]);
        }

        // Create test vehicles if they don't exist
        $vehicles = [];
        for ($i = 1; $i <= 3; $i++) {
            $vehicles[] = Kendaraan::firstOrCreate([
                'no_pol_kendaraan' => "B-{$i}234-ABC"
            ], [
                'merk' => 'Hino',
                'tipe' => 'Ranger',
                'kapasitas' => 8000,
                'kapasitas_satuan' => 1, // Assuming Liter unit ID is 1
                'tanggal_awal_valid' => now(),
                'tanggal_akhir_valid' => now()->addYear(),
                'deskripsi' => "Test vehicle {$i}",
                'created_by' => $user->id,
            ]);
        }

        // Create test TBBM if it doesn't exist
        $tbbm = Tbbm::firstOrCreate([
            'kode' => 'TBBM-001'
        ], [
            'nama' => 'TBBM Test Location',
            'pic_nama' => 'TBBM Manager',
            'pic_phone' => '081234567890',
            'alamat' => 'Test TBBM Address',
            'created_by' => $user->id,
        ]);

        // Create test sales orders with delivery orders
        for ($i = 1; $i <= 10; $i++) {
            $customer = $customers[array_rand($customers)];
            $fuelItem = $fuelItems[array_rand($fuelItems)];
            $driver = $drivers[array_rand($drivers)];
            $vehicle = $vehicles[array_rand($vehicles)];

            // Create sales order
            $salesOrder = TransaksiPenjualan::create([
                'kode' => 'SO-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'tipe' => 'dagang',
                'tanggal' => now()->subDays(rand(0, 30)),
                'id_pelanggan' => $customer->id,
                'alamat' => "Delivery Address {$i}",
                'nomor_po' => "PO-{$i}-" . date('Ymd'),
                'top_pembayaran' => rand(0, 60),
                'id_tbbm' => $tbbm->id,
                'created_by' => $user->id,
            ]);

            // Create sales order detail
            PenjualanDetail::create([
                'id_transaksi_penjualan' => $salesOrder->id,
                'id_item' => $fuelItem->id,
                'volume_item' => rand(1000, 8000),
                'harga_jual' => rand(8000, 12000),
                'created_by' => $user->id,
            ]);

            // Create delivery order
            $deliveryOrder = DeliveryOrder::create([
                'kode' => 'DO-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'id_transaksi' => $salesOrder->id,
                'id_karyawan' => $driver->id,
                'id_kendaraan' => $vehicle->id,
                'tanggal_delivery' => now()->addDays(rand(1, 7)),
                'no_segel' => 'SEAL-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'status_muat' => ['pending', 'muat', 'selesai'][rand(0, 2)],
                'waktu_muat' => now()->addHours(rand(1, 24)),
                'waktu_selesai_muat' => now()->addHours(rand(25, 48)),
                'do_signatory_name' => "Signatory {$i}",
                'do_print_status' => rand(0, 1),
                'fuel_usage_notes' => "Fuel usage notes for delivery {$i}",
                'driver_allowance_amount' => rand(100000, 500000),
                'allowance_receipt_status' => rand(0, 1),
                'allowance_receipt_time' => now()->addHours(rand(1, 12)),
                'do_handover_status' => rand(0, 1),
                'do_handover_time' => now()->addHours(rand(13, 24)),
                'invoice_number' => rand(0, 1) ? 'INV-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) : null,
                'tax_invoice_number' => rand(0, 1) ? 'TAX-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) : null,
                'invoice_delivery_status' => rand(0, 1),
                'invoice_archive_status' => rand(0, 1),
                'invoice_confirmation_status' => rand(0, 1),
                'invoice_confirmation_time' => now()->addDays(rand(1, 5)),
                'payment_status' => ['pending', 'partial', 'paid', 'overdue'][rand(0, 3)],
                'created_by' => $user->id,
            ]);

            // Create driver delivery record
            PengirimanDriver::create([
                'id_do' => $deliveryOrder->id,
                'totalisator_awal' => rand(10000, 50000),
                'totalisator_tiba' => rand(50001, 100000),
                'waktu_mulai' => now()->addHours(rand(1, 12)),
                'waktu_tiba' => now()->addHours(rand(13, 24)),
                'foto_pengiriman' => rand(0, 1) ? 'delivery-photo-' . $i . '.jpg' : null,
                'totalisator_pool_return' => rand(100001, 150000),
                'waktu_pool_arrival' => now()->addHours(rand(25, 36)),
                'created_by' => $user->id,
            ]);

            // Create driver allowance record
            $allowance = UangJalan::create([
                'id_do' => $deliveryOrder->id,
                'nominal' => rand(100000, 500000),
                'status_kirim' => ['pending', 'kirim', 'ditolak'][rand(0, 2)],
                'status_terima' => ['pending', 'terima', 'ditolak'][rand(0, 2)],
                'id_karyawan' => $driver->id,
                'created_by' => $user->id,
            ]);

            // Create dummy proof files for some allowances (70% chance)
            if (rand(1, 100) <= 70) {
                $this->createDummyAllowanceProofs($allowance, $i);
            }
        }

        $this->command->info('Fuel delivery test data created successfully!');
    }

    /**
     * Create dummy proof files for allowance
     */
    private function createDummyAllowanceProofs(UangJalan $allowance, int $sequence): void
    {
        // Create directories if they don't exist
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('allowance-proofs/sending')) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('allowance-proofs/sending');
        }
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('allowance-proofs/receiving')) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('allowance-proofs/receiving');
        }

        // Create sending proof (80% chance)
        if (rand(1, 100) <= 80) {
            $sendingContent = "DUMMY SENDING PROOF\n";
            $sendingContent .= "Allowance ID: {$allowance->id}\n";
            $sendingContent .= "Amount: IDR " . number_format($allowance->nominal) . "\n";
            $sendingContent .= "Driver: " . ($allowance->karyawan->nama ?? 'Unknown') . "\n";
            $sendingContent .= "Status: {$allowance->status_kirim}\n";
            $sendingContent .= "Created: " . now() . "\n";
            $sendingContent .= "\nThis is a dummy proof file for testing purposes.\n";

            $sendingFilename = "allowance-proofs/sending/sending-proof-{$allowance->id}-{$sequence}.jpg";
            \Illuminate\Support\Facades\Storage::disk('public')->put($sendingFilename, $sendingContent);
            $allowance->update(['bukti_kirim' => $sendingFilename]);
        }

        // Create receiving proof (60% chance)
        if (rand(1, 100) <= 60) {
            $receivingContent = "DUMMY RECEIVING PROOF\n";
            $receivingContent .= "Allowance ID: {$allowance->id}\n";
            $receivingContent .= "Amount: IDR " . number_format($allowance->nominal) . "\n";
            $receivingContent .= "Driver: " . ($allowance->karyawan->nama ?? 'Unknown') . "\n";
            $receivingContent .= "Status: {$allowance->status_terima}\n";
            $receivingContent .= "Received: " . now() . "\n";
            $receivingContent .= "\nThis is a dummy proof file for testing purposes.\n";

            $receivingFilename = "allowance-proofs/receiving/receiving-proof-{$allowance->id}-{$sequence}.jpg";
            \Illuminate\Support\Facades\Storage::disk('public')->put($receivingFilename, $receivingContent);
            $allowance->update(['bukti_terima' => $receivingFilename]);
        }
    }
}
