<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\TransaksiPenjualan;
use App\Models\DeliveryOrder;
use App\Models\PengirimanDriver;
use App\Models\UangJalan;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Item;
use App\Models\PenjualanDetail;
use App\Models\Tbbm;
use App\Models\Kendaraan;
use App\Filament\Pages\SalesOrderTimelineDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SalesOrderTimelineComponentTest extends TestCase
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
        $this->fuelItem = Item::factory()->create(['name' => 'Premium']);
        $this->tbbm = Tbbm::factory()->create();
        $this->vehicle = Kendaraan::factory()->create();
    }

    /** @test */
    public function timeline_detail_component_generates_sales_order_created_event()
    {
        $salesOrder = $this->createSalesOrder();
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $events = $component->getTimelineEvents();
        
        $salesOrderEvent = $events->firstWhere('type', 'sales_order_created');
        
        $this->assertNotNull($salesOrderEvent);
        $this->assertEquals('Sales Order Created', $salesOrderEvent['title']);
        $this->assertEquals('blue', $salesOrderEvent['color']);
        $this->assertEquals($salesOrder->created_at, $salesOrderEvent['timestamp']);
        $this->assertEquals($salesOrder->kode, $salesOrderEvent['data']['so_number']);
        $this->assertEquals($this->customer->nama, $salesOrderEvent['data']['customer']);
    }

    /** @test */
    public function timeline_detail_component_generates_delivery_order_events()
    {
        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $events = $component->getTimelineEvents();
        
        $deliveryEvent = $events->firstWhere('type', 'delivery_order_created');
        
        $this->assertNotNull($deliveryEvent);
        $this->assertEquals('Delivery Order Created', $deliveryEvent['title']);
        $this->assertEquals('indigo', $deliveryEvent['color']);
        $this->assertEquals($deliveryOrder->kode, $deliveryEvent['data']['do_number']);
    }

    /** @test */
    public function timeline_detail_component_generates_loading_events_when_data_exists()
    {
        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder, [
            'waktu_muat' => now()->subHours(2),
            'waktu_selesai_muat' => now()->subHours(1),
        ]);
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $events = $component->getTimelineEvents();
        
        $loadingStartedEvent = $events->firstWhere('type', 'loading_started');
        $loadingCompletedEvent = $events->firstWhere('type', 'loading_completed');
        
        $this->assertNotNull($loadingStartedEvent);
        $this->assertNotNull($loadingCompletedEvent);
        $this->assertEquals('Loading Started', $loadingStartedEvent['title']);
        $this->assertEquals('Loading Completed', $loadingCompletedEvent['title']);
    }

    /** @test */
    public function timeline_detail_component_skips_loading_events_when_no_data()
    {
        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder); // No loading times
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $events = $component->getTimelineEvents();
        
        $loadingStartedEvent = $events->firstWhere('type', 'loading_started');
        $loadingCompletedEvent = $events->firstWhere('type', 'loading_completed');
        
        $this->assertNull($loadingStartedEvent);
        $this->assertNull($loadingCompletedEvent);
    }

    /** @test */
    public function timeline_detail_component_generates_allowance_events()
    {
        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        $allowance = $this->createDriverAllowance($deliveryOrder);
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $events = $component->getTimelineEvents();
        
        $allowanceEvent = $events->firstWhere('type', 'allowance_created');
        
        $this->assertNotNull($allowanceEvent);
        $this->assertEquals('Driver Allowance Created', $allowanceEvent['title']);
        $this->assertEquals('purple', $allowanceEvent['color']);
        $this->assertEquals('IDR ' . number_format($allowance->nominal), $allowanceEvent['data']['amount']);
    }

    /** @test */
    public function timeline_detail_component_generates_delivery_progress_events()
    {
        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        $delivery = $this->createDeliveryProgress($deliveryOrder);
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $events = $component->getTimelineEvents();
        
        $departedEvent = $events->firstWhere('type', 'delivery_departed');
        $arrivedEvent = $events->firstWhere('type', 'delivery_arrived');
        $completedEvent = $events->firstWhere('type', 'delivery_completed');
        
        $this->assertNotNull($departedEvent);
        $this->assertNotNull($arrivedEvent);
        $this->assertNotNull($completedEvent);
        
        $this->assertEquals('Delivery Departed', $departedEvent['title']);
        $this->assertEquals('Delivery Arrived', $arrivedEvent['title']);
        $this->assertEquals('Delivery Completed', $completedEvent['title']);
    }

    /** @test */
    public function timeline_detail_component_sorts_events_chronologically()
    {
        $salesOrder = $this->createSalesOrder(['created_at' => now()->subDays(1)]);
        $deliveryOrder = $this->createDeliveryOrder($salesOrder, [
            'created_at' => now()->subHours(12),
            'waktu_muat' => now()->subHours(6),
            'waktu_selesai_muat' => now()->subHours(4),
        ]);
        $delivery = $this->createDeliveryProgress($deliveryOrder, [
            'waktu_berangkat' => now()->subHours(3),
            'waktu_tiba' => now()->subHours(1),
            'waktu_selesai' => now(),
        ]);
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $events = $component->getTimelineEvents();
        
        // Verify events are sorted by timestamp
        $timestamps = $events->pluck('timestamp');
        $sortedTimestamps = $timestamps->sort();
        
        $this->assertEquals($sortedTimestamps->values()->toArray(), $timestamps->toArray());
    }

    /** @test */
    public function timeline_detail_component_handles_multiple_delivery_orders()
    {
        $salesOrder = $this->createSalesOrder();
        $deliveryOrder1 = $this->createDeliveryOrder($salesOrder, ['kode' => 'DO-001']);
        $deliveryOrder2 = $this->createDeliveryOrder($salesOrder, ['kode' => 'DO-002']);
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $events = $component->getTimelineEvents();
        
        $deliveryEvents = $events->where('type', 'delivery_order_created');
        
        $this->assertCount(2, $deliveryEvents);
        
        $doNumbers = $deliveryEvents->pluck('data.do_number')->toArray();
        $this->assertContains('DO-001', $doNumbers);
        $this->assertContains('DO-002', $doNumbers);
    }

    /** @test */
    public function timeline_detail_component_handles_missing_relationships_gracefully()
    {
        $salesOrder = $this->createSalesOrder();
        // Create delivery order without driver or vehicle
        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'id_user' => null,
            'id_kendaraan' => null,
        ]);
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $events = $component->getTimelineEvents();
        
        $deliveryEvent = $events->firstWhere('type', 'delivery_order_created');
        
        $this->assertNotNull($deliveryEvent);
        $this->assertEquals('Not assigned', $deliveryEvent['data']['driver']);
        $this->assertEquals('Not assigned', $deliveryEvent['data']['vehicle']);
    }

    /** @test */
    public function get_delivery_orders_method_returns_correct_data()
    {
        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = $this->createDeliveryOrder($salesOrder);
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $deliveryOrders = $component->getDeliveryOrders();
        
        $this->assertCount(1, $deliveryOrders);
        $this->assertEquals($deliveryOrder->id, $deliveryOrders->first()->id);
        $this->assertTrue($deliveryOrders->first()->relationLoaded('user'));
        $this->assertTrue($deliveryOrders->first()->relationLoaded('kendaraan'));
    }

    /** @test */
    public function timeline_component_performance_with_large_dataset()
    {
        $salesOrder = $this->createSalesOrder();
        
        // Create multiple delivery orders with full data
        for ($i = 0; $i < 10; $i++) {
            $deliveryOrder = $this->createDeliveryOrder($salesOrder, [
                'kode' => "DO-{$i}",
                'waktu_muat' => now()->subDays($i)->subHours(2),
                'waktu_selesai_muat' => now()->subDays($i)->subHours(1),
            ]);
            
            $this->createDriverAllowance($deliveryOrder);
            $this->createDeliveryProgress($deliveryOrder, [
                'waktu_berangkat' => now()->subDays($i)->subHours(1),
                'waktu_tiba' => now()->subDays($i)->addMinutes(30),
                'waktu_selesai' => now()->subDays($i)->addHours(1),
            ]);
        }
        
        $component = new SalesOrderTimelineDetail();
        $component->record = $salesOrder;
        
        $startTime = microtime(true);
        $events = $component->getTimelineEvents();
        $endTime = microtime(true);
        
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time (less than 1 second)
        $this->assertLessThan(1.0, $executionTime);
        
        // Should have all expected events
        $this->assertGreaterThan(30, $events->count()); // At least 31 events (1 SO + 30 from DOs)
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
            'volume_item' => 1000,
            'created_by' => $this->user->id,
        ]);

        return $salesOrder;
    }

    protected function createDeliveryOrder(TransaksiPenjualan $salesOrder, array $attributes = []): DeliveryOrder
    {
        return DeliveryOrder::factory()->create(array_merge([
            'id_transaksi' => $salesOrder->id,
            'id_user' => $this->user->id,
            'id_kendaraan' => $this->vehicle->id,
            'created_by' => $this->user->id,
        ], $attributes));
    }

    protected function createDriverAllowance(DeliveryOrder $deliveryOrder): UangJalan
    {
        return UangJalan::factory()->create([
            'id_do' => $deliveryOrder->id,
            'nominal' => 500000,
            'id_user' => $this->user->id,
            'created_by' => $this->user->id,
        ]);
    }

    protected function createDeliveryProgress(DeliveryOrder $deliveryOrder, array $attributes = []): PengirimanDriver
    {
        return PengirimanDriver::factory()->create(array_merge([
            'id_do' => $deliveryOrder->id,
        ], $attributes));
    }
}
