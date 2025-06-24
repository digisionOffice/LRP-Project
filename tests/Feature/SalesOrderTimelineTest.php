<?php

namespace Tests\Feature;

use Tests\TestCase;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use App\Filament\Pages\SalesOrderTimeline;
use App\Filament\Pages\SalesOrderTimelineDetail;

class SalesOrderTimelineTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $adminUser;
    protected User $driverUser;
    protected Pelanggan $customer;
    protected Item $fuelItem;
    protected Tbbm $tbbm;
    protected Kendaraan $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Test Admin',
        ]);

        $this->driverUser = User::factory()->create([
            'email' => 'driver@test.com',
            'name' => 'Test Driver',
        ]);

        // Create test customer
        $this->customer = Pelanggan::factory()->create([
            'nama' => 'Test Customer',
            'kode' => 'CUST-001',
        ]);

        // Create test fuel item
        $this->fuelItem = Item::factory()->create([
            'name' => 'Premium',
            'kode' => 'FUEL-001',
        ]);

        // Create test TBBM
        $this->tbbm = Tbbm::factory()->create([
            'nama' => 'Test TBBM',
        ]);

        // Create test vehicle
        $this->vehicle = Kendaraan::factory()->create([
            'nomor_polisi' => 'B-1234-ABC',
        ]);
    }

    /** @test */
    public function authenticated_admin_can_access_sales_order_timeline_page()
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/sales-order-timeline');

        $response->assertStatus(200);
        $response->assertSee('Sales Orders');
        $response->assertSee('View sales orders and their complete delivery timeline');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_sales_order_timeline_page()
    {
        $response = $this->get('/admin/sales-order-timeline');

        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function sales_order_timeline_displays_sales_orders_in_table()
    {
        $this->actingAs($this->adminUser);

        // Create test sales order
        $salesOrder = $this->createSalesOrder();

        $component = Livewire::test(SalesOrderTimeline::class);

        $component->assertSee($salesOrder->kode);
        $component->assertSee($this->customer->nama);
    }

    /** @test */
    public function sales_order_timeline_can_filter_by_customer()
    {
        $this->actingAs($this->adminUser);

        // Create sales orders for different customers
        $salesOrder1 = $this->createSalesOrder();

        $customer2 = Pelanggan::factory()->create(['nama' => 'Customer 2']);
        $salesOrder2 = $this->createSalesOrder(['id_pelanggan' => $customer2->id]);

        $component = Livewire::test(SalesOrderTimeline::class);

        // Filter by first customer
        $component->filterTable('id_pelanggan', $this->customer->id);

        $component->assertSee($salesOrder1->kode);
        $component->assertDontSee($salesOrder2->kode);
    }

    /** @test */
    public function sales_order_timeline_can_filter_by_date_range()
    {
        $this->actingAs($this->adminUser);

        // Create sales orders with different dates
        $oldSalesOrder = $this->createSalesOrder(['tanggal' => now()->subDays(10)]);
        $newSalesOrder = $this->createSalesOrder(['tanggal' => now()]);

        $component = Livewire::test(SalesOrderTimeline::class);

        // Filter by recent dates only
        $component->filterTable('tanggal', [
            'from' => now()->subDays(5)->format('Y-m-d'),
            'until' => now()->format('Y-m-d'),
        ]);

        $component->assertSee($newSalesOrder->kode);
        $component->assertDontSee($oldSalesOrder->kode);
    }

    /** @test */
    public function view_timeline_action_redirects_to_detail_page()
    {
        $this->actingAs($this->adminUser);

        $salesOrder = $this->createSalesOrder();

        $component = Livewire::test(SalesOrderTimeline::class);

        // Check that the view timeline action exists and has correct URL
        $component->assertTableActionExists('view_timeline');

        // The action should have a URL that points to the detail page
        $expectedUrl = "/admin/sales-order-timeline-detail?record={$salesOrder->id}";
        $component->assertTableActionHasUrl('view_timeline', $expectedUrl, $salesOrder);
    }

    /** @test */
    public function timeline_detail_page_displays_sales_order_information()
    {
        $this->actingAs($this->adminUser);

        $salesOrder = $this->createSalesOrder();

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee("Sales Order: {$salesOrder->kode}");
        $response->assertSee($this->customer->nama);
        $response->assertSee($this->fuelItem->name);
        $response->assertSee($this->tbbm->nama);
    }

    /** @test */
    public function timeline_detail_page_shows_404_for_nonexistent_sales_order()
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/sales-order-timeline-detail?record=99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function timeline_detail_page_displays_delivery_order_events()
    {
        $this->actingAs($this->adminUser);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertSee('Delivery Order Created');
        $response->assertSee($deliveryOrder->kode);
        $response->assertSee($this->vehicle->nomor_polisi);
        $response->assertSee($this->driverUser->name);
    }

    /** @test */
    public function timeline_detail_page_displays_loading_events_when_available()
    {
        $this->actingAs($this->adminUser);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder, [
            'waktu_muat' => now()->subHours(2),
            'waktu_selesai_muat' => now()->subHours(1),
            'status_muat' => 'selesai',
        ]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertSee('Loading Started');
        $response->assertSee('Loading Completed');
    }

    /** @test */
    public function timeline_detail_page_displays_driver_allowance_events()
    {
        $this->actingAs($this->adminUser);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        $allowance = $this->createDriverAllowance($deliveryOrder);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertSee('Driver Allowance Created');
        $response->assertSee('IDR ' . number_format($allowance->nominal));
    }

    /** @test */
    public function timeline_detail_page_displays_delivery_progress_events()
    {
        $this->actingAs($this->adminUser);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        $delivery = $this->createDeliveryProgress($deliveryOrder);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertSee('Delivery Departed');
        $response->assertSee('Delivery Arrived');
        $response->assertSee('Delivery Completed');
    }

    /** @test */
    public function timeline_events_are_sorted_chronologically()
    {
        $this->actingAs($this->adminUser);

        $salesOrder = $this->createSalesOrder(['tanggal' => now()->subDays(1)]);
        $deliveryOrder = $this->createDeliveryOrder($salesOrder, [
            'created_at' => now()->subHours(12),
            'waktu_muat' => now()->subHours(6),
            'waktu_selesai_muat' => now()->subHours(4),
        ]);

        // Test the timeline events directly through the page
        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");
        $response->assertStatus(200);

        // Create component instance to test event ordering
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        $events = $component->getTimelineEvents();

        // Verify events are in chronological order
        $timestamps = $events->pluck('timestamp')->toArray();
        $sortedTimestamps = collect($timestamps)->sort()->values()->toArray();

        $this->assertEquals($sortedTimestamps, $timestamps);
    }

    /** @test */
    public function timeline_handles_empty_data_gracefully()
    {
        $this->actingAs($this->adminUser);

        // Create sales order without any delivery orders
        $salesOrder = $this->createSalesOrder();

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('Sales Order Created');
        // Should only show the sales order creation event
    }

    protected function createSalesOrder(array $attributes = []): TransaksiPenjualan
    {
        $salesOrder = TransaksiPenjualan::factory()->create(array_merge([
            'kode' => 'SO-' . $this->faker->unique()->numberBetween(1000, 9999),
            'id_pelanggan' => $this->customer->id,
            'id_tbbm' => $this->tbbm->id,
            'tanggal' => now(),
            'created_by' => $this->adminUser->id,
        ], $attributes));

        // Create sales order details
        PenjualanDetail::factory()->create([
            'id_transaksi_penjualan' => $salesOrder->id,
            'id_item' => $this->fuelItem->id,
            'volume_item' => 1000,
            'harga_jual' => 15000,
            'created_by' => $this->adminUser->id,
        ]);

        return $salesOrder;
    }

    protected function createDeliveryOrder(TransaksiPenjualan $salesOrder, array $attributes = []): DeliveryOrder
    {
        return DeliveryOrder::factory()->create(array_merge([
            'kode' => 'DO-' . $this->faker->unique()->numberBetween(1000, 9999),
            'id_transaksi' => $salesOrder->id,
            'id_user' => $this->driverUser->id,
            'id_kendaraan' => $this->vehicle->id,
            'tanggal_delivery' => now()->addDay(),
            'no_segel' => 'SEAL-' . $this->faker->numberBetween(1000, 9999),
            'created_by' => $this->adminUser->id,
        ], $attributes));
    }

    protected function createDriverAllowance(DeliveryOrder $deliveryOrder): UangJalan
    {
        return UangJalan::factory()->create([
            'id_do' => $deliveryOrder->id,
            'nominal' => 500000,
            'status_kirim' => 'kirim',
            'status_terima' => 'terima',
            'id_user' => $this->driverUser->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    protected function createDeliveryProgress(DeliveryOrder $deliveryOrder): PengirimanDriver
    {
        return PengirimanDriver::factory()->create([
            'id_do' => $deliveryOrder->id,
            'waktu_berangkat' => now()->subHours(3),
            'waktu_tiba' => now()->subHours(1),
            'waktu_selesai' => now(),
            'volume_terkirim' => 1000,
        ]);
    }
}
