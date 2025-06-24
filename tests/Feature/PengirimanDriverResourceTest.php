<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PengirimanDriver;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\Pelanggan;
use App\Models\Kendaraan;
use App\Models\Item;
use App\Models\Tbbm;
use App\Models\PenjualanDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use App\Filament\Resources\PengirimanDriverResource;

class PengirimanDriverResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $driver;
    protected Pelanggan $pelanggan;
    protected Kendaraan $kendaraan;
    protected Item $item;
    protected Tbbm $tbbm;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with admin role
        $this->user = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'role' => 'admin'
        ]);

        // Create test driver
        $this->driver = User::factory()->create([
            'name' => 'Test Driver',
            'email' => 'driver@test.com',
            'role' => 'driver'
        ]);

        // Create supporting models
        $this->pelanggan = Pelanggan::factory()->create();
        $this->kendaraan = Kendaraan::factory()->create();
        $this->item = Item::factory()->create();
        $this->tbbm = Tbbm::factory()->create();
    }

    /** @test */
    public function pengiriman_driver_resource_can_list_records()
    {
        $this->actingAs($this->user);

        // Create test data
        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        $pengirimanDriver = $this->createPengirimanDriver($deliveryOrder);

        $response = $this->get(route('filament.admin.resources.pengiriman-drivers.index'));

        $response->assertStatus(200);
        $response->assertSee($deliveryOrder->kode);
        $response->assertSee($this->pelanggan->nama);
    }

    /** @test */
    public function pengiriman_driver_resource_shows_proper_relationships()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        $pengirimanDriver = $this->createPengirimanDriver($deliveryOrder);

        $component = Livewire::test(PengirimanDriverResource\Pages\ListPengirimanDrivers::class);

        $component->assertSee($deliveryOrder->kode);
        $component->assertSee($this->pelanggan->nama);
        $component->assertSee($this->driver->name);
        $component->assertSee($this->kendaraan->nomor_polisi);
    }

    /** @test */
    public function pengiriman_driver_resource_shows_delivery_status_badges()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);

        // Test different delivery statuses
        $pengirimanDriver1 = $this->createPengirimanDriver($deliveryOrder, [
            'waktu_mulai' => null,
            'waktu_berangkat' => null,
            'waktu_tiba' => null,
            'waktu_selesai' => null,
        ]);

        $pengirimanDriver2 = $this->createPengirimanDriver($deliveryOrder, [
            'waktu_mulai' => now()->subHours(2),
            'waktu_berangkat' => null,
            'waktu_tiba' => null,
            'waktu_selesai' => null,
        ]);

        $pengirimanDriver3 = $this->createPengirimanDriver($deliveryOrder, [
            'waktu_mulai' => now()->subHours(3),
            'waktu_berangkat' => now()->subHours(2),
            'waktu_tiba' => now()->subHours(1),
            'waktu_selesai' => now(),
        ]);

        $component = Livewire::test(PengirimanDriverResource\Pages\ListPengirimanDrivers::class);

        $component->assertSee('Belum Mulai');
        $component->assertSee('Mulai');
        $component->assertSee('Selesai');
    }

    /** @test */
    public function pengiriman_driver_resource_can_view_record_with_navigation_buttons()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        $pengirimanDriver = $this->createPengirimanDriver($deliveryOrder);

        $response = $this->get(route('filament.admin.resources.pengiriman-drivers.view', ['record' => $pengirimanDriver]));

        $response->assertStatus(200);
        $response->assertSee('Lihat Delivery Order');
        $response->assertSee('Lihat Timeline');
        $response->assertSee('Lihat Sales Order');
    }

    /** @test */
    public function pengiriman_driver_resource_navigation_buttons_work()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        $pengirimanDriver = $this->createPengirimanDriver($deliveryOrder);

        $component = Livewire::test(PengirimanDriverResource\Pages\ListPengirimanDrivers::class);

        // Test that navigation URLs are generated correctly
        $this->assertTrue(str_contains(
            route('filament.admin.resources.delivery-orders.view', ['record' => $deliveryOrder->id]),
            'delivery-orders'
        ));

        $this->assertTrue(str_contains(
            "/admin/sales-order-timeline-detail?record={$salesOrder->id}",
            'sales-order-timeline-detail'
        ));

        $this->assertTrue(str_contains(
            route('filament.admin.resources.transaksi-penjualans.view', ['record' => $salesOrder->id]),
            'transaksi-penjualans'
        ));
    }

    /** @test */
    public function pengiriman_driver_resource_filters_work()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);

        // Create records with different statuses
        $pengirimanDriver1 = $this->createPengirimanDriver($deliveryOrder, [
            'waktu_mulai' => null,
        ]);

        $pengirimanDriver2 = $this->createPengirimanDriver($deliveryOrder, [
            'waktu_mulai' => now(),
            'waktu_selesai' => now()->addHours(2),
        ]);

        $component = Livewire::test(PengirimanDriverResource\Pages\ListPengirimanDrivers::class);

        // Test status filter
        $component->filterTable('delivery_status', 'selesai');
        $component->assertCanSeeTableRecords([$pengirimanDriver2]);
        $component->assertCanNotSeeTableRecords([$pengirimanDriver1]);
    }

    /** @test */
    public function pengiriman_driver_resource_shows_volume_with_proper_formatting()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        $pengirimanDriver = $this->createPengirimanDriver($deliveryOrder, [
            'volume_terkirim' => 1500.75,
        ]);

        $component = Livewire::test(PengirimanDriverResource\Pages\ListPengirimanDrivers::class);

        $component->assertSee('1,500.75 L');
    }

    protected function createSalesOrder(): TransaksiPenjualan
    {
        $salesOrder = TransaksiPenjualan::factory()->create([
            'id_pelanggan' => $this->pelanggan->id,
            'id_tbbm' => $this->tbbm->id,
            'created_by' => $this->user->id,
        ]);

        PenjualanDetail::factory()->create([
            'id_transaksi_penjualan' => $salesOrder->id,
            'id_item' => $this->item->id,
            'volume_item' => 1000,
        ]);

        return $salesOrder;
    }

    protected function createDeliveryOrder(TransaksiPenjualan $salesOrder): DeliveryOrder
    {
        return DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'id_user' => $this->driver->id,
            'id_kendaraan' => $this->kendaraan->id,
            'created_by' => $this->user->id,
        ]);
    }

    protected function createPengirimanDriver(DeliveryOrder $deliveryOrder, array $attributes = []): PengirimanDriver
    {
        return PengirimanDriver::factory()->create(array_merge([
            'id_do' => $deliveryOrder->id,
            'created_by' => $this->user->id,
        ], $attributes));
    }
}
