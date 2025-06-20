<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\PenjualanDetail;
use App\Models\Item;
use App\Models\Pelanggan;
use App\Models\Kendaraan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryOrderVolumeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_calculate_total_so_volume()
    {
        // Create sales order with details
        $salesOrder = TransaksiPenjualan::factory()->create();
        $item = Item::factory()->create();
        
        PenjualanDetail::factory()->create([
            'id_transaksi_penjualan' => $salesOrder->id,
            'id_item' => $item->id,
            'volume_item' => 1000,
        ]);
        
        PenjualanDetail::factory()->create([
            'id_transaksi_penjualan' => $salesOrder->id,
            'id_item' => $item->id,
            'volume_item' => 500,
        ]);

        // Create delivery order
        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
        ]);

        // Test total SO volume calculation
        $this->assertEquals(1500, $deliveryOrder->total_so_volume);
    }

    /** @test */
    public function it_can_calculate_remaining_volume()
    {
        // Create sales order with details
        $salesOrder = TransaksiPenjualan::factory()->create();
        $item = Item::factory()->create();
        
        PenjualanDetail::factory()->create([
            'id_transaksi_penjualan' => $salesOrder->id,
            'id_item' => $item->id,
            'volume_item' => 2000,
        ]);

        // Create first delivery order
        $deliveryOrder1 = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'volume_do' => 800,
        ]);

        // Create second delivery order
        $deliveryOrder2 = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'volume_do' => 600,
        ]);

        // Test remaining volume calculation for second delivery order
        $remainingVolume = $deliveryOrder2->calculateRemainingVolume();
        $this->assertEquals(600, $remainingVolume); // 2000 - 800 - 600 = 600
    }

    /** @test */
    public function it_stores_volume_fields_correctly()
    {
        $salesOrder = TransaksiPenjualan::factory()->create();
        
        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'volume_do' => 1500.5,
            'sisa_volume_do' => 500.25,
        ]);

        $this->assertDatabaseHas('delivery_order', [
            'id' => $deliveryOrder->id,
            'volume_do' => 1500.5,
            'sisa_volume_do' => 500.25,
        ]);
    }
}
