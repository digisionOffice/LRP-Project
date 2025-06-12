<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TransaksiPenjualan;
use App\Models\DeliveryOrder;
use App\Models\PengirimanDriver;
use App\Models\UangJalan;
use App\Models\Pelanggan;
use App\Models\Item;
use App\Models\PenjualanDetail;
use App\Models\Tbbm;
use App\Models\Kendaraan;

class SalesOrderTimelineTestSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Use existing admin user from UserSeeder
        $user = User::where('email', 'admin@lrp.com')->first();
        if (!$user) {
            $this->command->error('Admin user not found. Please run UserSeeder first.');
            return;
        }

        // Use existing driver user from UserSeeder
        $driver = User::where('email', 'driver@lrp.com')->first();
        if (!$driver) {
            $this->command->error('Driver user not found. Please run UserSeeder first.');
            return;
        }

        // Create test customer
        $customer = Pelanggan::firstOrCreate([
            'kode' => 'TIMELINE-CUST-001'
        ], [
            'type' => 'corporate',
            'nama' => 'Timeline Test Customer',
            'pic_nama' => 'Test PIC',
            'pic_phone' => '081234567890',
            'alamat' => 'Test Customer Address',
            'created_by' => $user->id,
        ]);

        // Create test fuel item
        $fuelItem = Item::firstOrCreate([
            'kode' => 'TIMELINE-FUEL-001'
        ], [
            'name' => 'Timeline Test Premium',
            'description' => 'Test fuel for timeline',
            'id_item_jenis' => 1,
            'id_satuan' => 1,
            'created_by' => $user->id,
        ]);

        // Create test TBBM
        $tbbm = Tbbm::firstOrCreate([
            'nama' => 'Timeline Test TBBM'
        ], [
            'alamat' => 'Test TBBM Address',
            'created_by' => $user->id,
        ]);

        // Create test vehicle
        $vehicle = Kendaraan::firstOrCreate([
            'nomor_polisi' => 'TIMELINE-001'
        ], [
            'merk' => 'Hino',
            'tipe' => 'Ranger',
            'kapasitas' => 8000,
            'kapasitas_satuan' => 1,
            'tanggal_awal_valid' => now(),
            'tanggal_akhir_valid' => now()->addYear(),
            'deskripsi' => 'Test vehicle for timeline',
            'created_by' => $user->id,
        ]);

        // Create comprehensive test sales order with complete timeline
        $salesOrder = TransaksiPenjualan::create([
            'kode' => 'TIMELINE-SO-001',
            'tipe' => 'dagang',
            'tanggal' => now()->subDays(5),
            'id_pelanggan' => $customer->id,
            'alamat' => 'Test Delivery Address',
            'nomor_po' => 'TIMELINE-PO-001',
            'top_pembayaran' => 30,
            'id_tbbm' => $tbbm->id,
            'created_by' => $user->id,
        ]);

        // Create sales order detail
        PenjualanDetail::create([
            'id_transaksi_penjualan' => $salesOrder->id,
            'id_item' => $fuelItem->id,
            'volume_item' => 2000,
            'harga_jual' => 15000,
            'created_by' => $user->id,
        ]);

        // Create delivery order with complete timeline
        $deliveryOrder = DeliveryOrder::create([
            'kode' => 'TIMELINE-DO-001',
            'id_transaksi' => $salesOrder->id,
            'id_user' => $driver->id,
            'id_kendaraan' => $vehicle->id,
            'tanggal_delivery' => now()->subDays(3),
            'no_segel' => 'TIMELINE-SEAL-001',
            'status_muat' => 'selesai',
            'waktu_muat' => now()->subDays(3)->addHours(8),
            'waktu_selesai_muat' => now()->subDays(3)->addHours(10),
            'created_by' => $user->id,
        ]);

        // Create driver allowance
        $allowance = UangJalan::create([
            'id_do' => $deliveryOrder->id,
            'nominal' => 750000,
            'status_kirim' => 'kirim',
            'status_terima' => 'terima',
            'id_user' => $driver->id,
            'created_by' => $user->id,
        ]);

        // Create delivery progress with complete timeline
        $delivery = PengirimanDriver::create([
            'id_do' => $deliveryOrder->id,
            'waktu_berangkat' => now()->subDays(3)->addHours(11),
            'waktu_tiba' => now()->subDays(3)->addHours(15),
            'waktu_selesai' => now()->subDays(3)->addHours(17),
            'volume_terkirim' => 2000,
            'totalisator_awal' => 25000,
            'totalisator_tiba' => 27000,
            'created_by' => $user->id,
        ]);

        // Create additional test scenarios
        $this->createEdgeCaseScenarios($user, $driver, $customer, $fuelItem, $tbbm, $vehicle);

        $this->command->info('Sales Order Timeline test data created successfully!');
    }

    private function createEdgeCaseScenarios($user, $driver, $customer, $fuelItem, $tbbm, $vehicle)
    {
        // Scenario 1: Sales order without delivery order
        $soWithoutDO = TransaksiPenjualan::create([
            'kode' => 'TIMELINE-SO-002',
            'tipe' => 'dagang',
            'tanggal' => now()->subDays(2),
            'id_pelanggan' => $customer->id,
            'alamat' => 'Test Address 2',
            'nomor_po' => 'TIMELINE-PO-002',
            'id_tbbm' => $tbbm->id,
            'created_by' => $user->id,
        ]);

        PenjualanDetail::create([
            'id_transaksi_penjualan' => $soWithoutDO->id,
            'id_item' => $fuelItem->id,
            'volume_item' => 1000,
            'harga_jual' => 15000,
            'created_by' => $user->id,
        ]);

        // Scenario 2: Delivery order without loading times
        $soPartial = TransaksiPenjualan::create([
            'kode' => 'TIMELINE-SO-003',
            'tipe' => 'dagang',
            'tanggal' => now()->subDays(1),
            'id_pelanggan' => $customer->id,
            'alamat' => 'Test Address 3',
            'nomor_po' => 'TIMELINE-PO-003',
            'id_tbbm' => $tbbm->id,
            'created_by' => $user->id,
        ]);

        PenjualanDetail::create([
            'id_transaksi_penjualan' => $soPartial->id,
            'id_item' => $fuelItem->id,
            'volume_item' => 1500,
            'harga_jual' => 15000,
            'created_by' => $user->id,
        ]);

        DeliveryOrder::create([
            'kode' => 'TIMELINE-DO-003',
            'id_transaksi' => $soPartial->id,
            'id_user' => $driver->id,
            'id_kendaraan' => $vehicle->id,
            'tanggal_delivery' => now()->addDay(),
            'no_segel' => 'TIMELINE-SEAL-003',
            'status_muat' => 'pending',
            'created_by' => $user->id,
        ]);

        // Scenario 3: Multiple delivery orders for one sales order
        $soMultiple = TransaksiPenjualan::create([
            'kode' => 'TIMELINE-SO-004',
            'tipe' => 'dagang',
            'tanggal' => now()->subDays(4),
            'id_pelanggan' => $customer->id,
            'alamat' => 'Test Address 4',
            'nomor_po' => 'TIMELINE-PO-004',
            'id_tbbm' => $tbbm->id,
            'created_by' => $user->id,
        ]);

        PenjualanDetail::create([
            'id_transaksi_penjualan' => $soMultiple->id,
            'id_item' => $fuelItem->id,
            'volume_item' => 3000,
            'harga_jual' => 15000,
            'created_by' => $user->id,
        ]);

        // First delivery order
        DeliveryOrder::create([
            'kode' => 'TIMELINE-DO-004A',
            'id_transaksi' => $soMultiple->id,
            'id_user' => $driver->id,
            'id_kendaraan' => $vehicle->id,
            'tanggal_delivery' => now()->subDays(4),
            'no_segel' => 'TIMELINE-SEAL-004A',
            'status_muat' => 'selesai',
            'waktu_muat' => now()->subDays(4)->addHours(8),
            'waktu_selesai_muat' => now()->subDays(4)->addHours(10),
            'created_by' => $user->id,
        ]);

        // Second delivery order
        DeliveryOrder::create([
            'kode' => 'TIMELINE-DO-004B',
            'id_transaksi' => $soMultiple->id,
            'id_user' => $driver->id,
            'id_kendaraan' => $vehicle->id,
            'tanggal_delivery' => now()->subDays(3),
            'no_segel' => 'TIMELINE-SEAL-004B',
            'status_muat' => 'selesai',
            'waktu_muat' => now()->subDays(3)->addHours(8),
            'waktu_selesai_muat' => now()->subDays(3)->addHours(10),
            'created_by' => $user->id,
        ]);
    }
}
