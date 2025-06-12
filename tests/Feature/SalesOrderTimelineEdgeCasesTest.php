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
use App\Filament\Pages\SalesOrderTimelineDetail;

class SalesOrderTimelineEdgeCasesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Pelanggan $customer;
    protected Item $fuelItem;
    protected Tbbm $tbbm;
    protected Kendaraan $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Pelanggan::factory()->create();
        $this->fuelItem = Item::factory()->create();
        $this->tbbm = Tbbm::factory()->create();
        $this->vehicle = Kendaraan::factory()->create();
    }

    /** @test */
    public function timeline_handles_sales_order_with_no_delivery_orders()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('Sales Order Created');
        $response->assertDontSee('Delivery Order Created');
    }

    /** @test */
    public function timeline_handles_delivery_order_with_missing_relationships()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        // Create delivery order without driver and vehicle
        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'id_user' => null,
            'id_kendaraan' => null,
            'kode' => 'DO-ORPHAN-001',
        ]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('Delivery Order Created');
        $response->assertSee('DO-ORPHAN-001');
        $response->assertSee('Not assigned'); // Should show for missing driver/vehicle
    }

    /** @test */
    public function timeline_handles_partial_loading_data()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        // Create delivery order with only start loading time
        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'waktu_muat' => now()->subHours(2),
            'waktu_selesai_muat' => null, // No completion time
            'status_muat' => 'muat',
        ]);

        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;

        $events = $component->getTimelineEvents();

        $loadingStartedEvent = $events->firstWhere('type', 'loading_started');
        $loadingCompletedEvent = $events->firstWhere('type', 'loading_completed');

        $this->assertNotNull($loadingStartedEvent);
        $this->assertNull($loadingCompletedEvent); // Should not exist
    }

    /** @test */
    public function timeline_handles_partial_delivery_progress()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = DeliveryOrder::factory()->create(['id_transaksi' => $salesOrder->id]);

        // Create delivery progress with only departure time
        PengirimanDriver::factory()->create([
            'id_do' => $deliveryOrder->id,
            'waktu_berangkat' => now()->subHours(3),
            'waktu_tiba' => null,
            'waktu_selesai' => null,
        ]);

        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;

        $events = $component->getTimelineEvents();

        $departedEvent = $events->firstWhere('type', 'delivery_departed');
        $arrivedEvent = $events->firstWhere('type', 'delivery_arrived');
        $completedEvent = $events->firstWhere('type', 'delivery_completed');

        $this->assertNotNull($departedEvent);
        $this->assertNull($arrivedEvent);
        $this->assertNull($completedEvent);
    }

    /** @test */
    public function timeline_handles_invalid_date_ranges()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = DeliveryOrder::factory()->create(['id_transaksi' => $salesOrder->id]);

        // Create delivery progress with invalid date sequence
        PengirimanDriver::factory()->create([
            'id_do' => $deliveryOrder->id,
            'waktu_berangkat' => now(),
            'waktu_tiba' => now()->subHours(1), // Arrival before departure
            'waktu_selesai' => now()->subHours(2), // Completion before arrival
        ]);

        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;

        // Should not throw exception even with invalid dates
        $events = $component->getTimelineEvents();

        $this->assertGreaterThan(0, $events->count());
    }

    /** @test */
    public function timeline_handles_large_number_of_delivery_orders()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        // Create 20 delivery orders
        for ($i = 0; $i < 20; $i++) {
            $deliveryOrder = DeliveryOrder::factory()->create([
                'id_transaksi' => $salesOrder->id,
                'kode' => "DO-BULK-{$i}",
                'waktu_muat' => now()->subDays($i)->subHours(2),
                'waktu_selesai_muat' => now()->subDays($i)->subHours(1),
            ]);

            UangJalan::factory()->create(['id_do' => $deliveryOrder->id]);

            PengirimanDriver::factory()->create([
                'id_do' => $deliveryOrder->id,
                'waktu_berangkat' => now()->subDays($i)->subHours(1),
                'waktu_tiba' => now()->subDays($i)->addMinutes(30),
                'waktu_selesai' => now()->subDays($i)->addHours(1),
            ]);
        }

        $startTime = microtime(true);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        // Should complete within reasonable time (less than 2 seconds)
        $this->assertLessThan(2.0, $executionTime);

        // Should display all delivery orders
        for ($i = 0; $i < 5; $i++) { // Check first 5
            $response->assertSee("DO-BULK-{$i}");
        }
    }

    /** @test */
    public function timeline_handles_concurrent_events_with_same_timestamp()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $sameTimestamp = now()->subHours(2);

        // Create multiple delivery orders with same timestamp
        for ($i = 0; $i < 3; $i++) {
            DeliveryOrder::factory()->create([
                'id_transaksi' => $salesOrder->id,
                'kode' => "DO-CONCURRENT-{$i}",
                'created_at' => $sameTimestamp,
            ]);
        }

        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;

        $events = $component->getTimelineEvents();

        // Should handle concurrent events without errors
        $deliveryEvents = $events->where('type', 'delivery_order_created');
        $this->assertCount(3, $deliveryEvents);
    }

    /** @test */
    public function timeline_handles_null_and_empty_values_gracefully()
    {
        $this->actingAs($this->user);

        // Create sales order with minimal data
        $salesOrder = TransaksiPenjualan::factory()->create([
            'id_pelanggan' => null,
            'id_tbbm' => null,
            'alamat' => null,
            'nomor_po' => null,
        ]);

        // Create delivery order with minimal data
        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'id_user' => null,
            'id_kendaraan' => null,
            'no_segel' => null,
            'tanggal_delivery' => null,
        ]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('N/A'); // Should show N/A for missing data
        $response->assertSee('Not assigned');
        $response->assertSee('Not scheduled');
    }

    /** @test */
    public function timeline_filters_work_with_empty_results()
    {
        $this->actingAs($this->user);

        // Don't create any sales orders
        $response = $this->get('/admin/sales-order-timeline');

        $response->assertStatus(200);
        $response->assertSee('Sales Orders');
        // Should not show any data but also not error
    }

    /** @test */
    public function timeline_detail_page_breadcrumbs_work_correctly()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;

        $breadcrumbs = $component->getBreadcrumbs();

        $this->assertArrayHasKey(route('filament.admin.pages.sales-order-timeline'), $breadcrumbs);
        $this->assertEquals('Sales Order Timeline', $breadcrumbs[route('filament.admin.pages.sales-order-timeline')]);
        $this->assertContains("SO: {$salesOrder->kode}", array_values($breadcrumbs));
    }

    /** @test */
    public function timeline_component_memory_usage_is_reasonable()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        // Create moderate amount of data
        for ($i = 0; $i < 5; $i++) {
            $deliveryOrder = DeliveryOrder::factory()->create(['id_transaksi' => $salesOrder->id]);
            UangJalan::factory()->create(['id_do' => $deliveryOrder->id]);
            PengirimanDriver::factory()->create(['id_do' => $deliveryOrder->id]);
        }

        $memoryBefore = memory_get_usage();

        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        $events = $component->getTimelineEvents();

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Should use less than 5MB of memory
        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed);

        // Should return reasonable number of events
        $this->assertGreaterThan(0, $events->count());
        $this->assertLessThan(50, $events->count()); // Reasonable upper limit
    }

    protected function createSalesOrder(array $attributes = []): TransaksiPenjualan
    {
        $salesOrder = TransaksiPenjualan::factory()->create(array_merge([
            'id_pelanggan' => $this->customer->id,
            'id_tbbm' => $this->tbbm->id,
            'created_by' => $this->user->id,
        ], $attributes));

        PenjualanDetail::factory()->create([
            'id_transaksi_penjualan' => $salesOrder->id,
            'id_item' => $this->fuelItem->id,
            'created_by' => $this->user->id,
        ]);

        return $salesOrder;
    }
}
