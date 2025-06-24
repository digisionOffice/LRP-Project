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

class SalesOrderTimelineViewTest extends TestCase
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
    public function timeline_detail_view_renders_sales_order_header_correctly()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder([
            'kode' => 'SO-VIEW-TEST-001',
            'tanggal' => now()->subDays(2),
        ]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('Sales Order: SO-VIEW-TEST-001');
        $response->assertSee('Complete delivery process timeline');
        $response->assertSee($salesOrder->tanggal->format('d M Y'));
        $response->assertSee($this->customer->nama);
    }

    /** @test */
    public function timeline_detail_view_displays_sales_order_summary()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('Customer');
        $response->assertSee('Fuel Types');
        $response->assertSee('Total Volume');
        $response->assertSee('TBBM');
        $response->assertSee($this->customer->nama);
        $response->assertSee($this->fuelItem->name);
        $response->assertSee($this->tbbm->nama);
    }

    /** @test */
    public function timeline_detail_view_shows_chronological_events()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder(['created_at' => now()->subDays(3)]);

        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'kode' => 'DO-CHRONO-001',
            'created_at' => now()->subDays(2),
            'waktu_muat' => now()->subDays(1)->subHours(2),
            'waktu_selesai_muat' => now()->subDays(1),
        ]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('Sales Order Created');
        $response->assertSee('Delivery Order Created');
        $response->assertSee('Loading Started');
        $response->assertSee('Loading Completed');

        // Check that events appear in chronological order in the HTML
        $content = $response->getContent();
        $salesOrderPos = strpos($content, 'Sales Order Created');
        $deliveryOrderPos = strpos($content, 'Delivery Order Created');
        $loadingStartPos = strpos($content, 'Loading Started');
        $loadingEndPos = strpos($content, 'Loading Completed');

        $this->assertLessThan($deliveryOrderPos, $salesOrderPos);
        $this->assertLessThan($loadingStartPos, $deliveryOrderPos);
        $this->assertLessThan($loadingEndPos, $loadingStartPos);
    }

    /** @test */
    public function timeline_detail_view_displays_event_icons_correctly()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = DeliveryOrder::factory()->create(['id_transaksi' => $salesOrder->id]);
        UangJalan::factory()->create(['id_do' => $deliveryOrder->id]);
        PengirimanDriver::factory()->create(['id_do' => $deliveryOrder->id]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);

        // Check for different colored timeline icons
        $response->assertSee('bg-blue-500'); // Sales order
        $response->assertSee('bg-indigo-500'); // Delivery order
        $response->assertSee('bg-purple-500'); // Allowance
        $response->assertSee('bg-orange-500'); // Departure
    }

    /** @test */
    public function timeline_detail_view_shows_event_details_in_cards()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();
        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $salesOrder->id,
            'kode' => 'DO-DETAIL-001',
            'no_segel' => 'SEAL-DETAIL-001',
        ]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('DO-DETAIL-001');
        $response->assertSee('SEAL-DETAIL-001');
        $response->assertSee('bg-gray-50'); // Event detail cards
    }

    /** @test */
    public function timeline_detail_view_handles_missing_data_gracefully()
    {
        $this->actingAs($this->user);

        $salesOrder = TransaksiPenjualan::factory()->create([
            'id_pelanggan' => null,
            'id_tbbm' => null,
        ]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('N/A');
    }

    /** @test */
    public function timeline_detail_view_shows_empty_state_when_no_events()
    {
        $this->actingAs($this->user);

        // Create sales order without any related data
        $salesOrder = TransaksiPenjualan::factory()->create([
            'id_pelanggan' => $this->customer->id,
            'id_tbbm' => $this->tbbm->id,
        ]);

        // Don't create PenjualanDetail to simulate empty state
        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('Sales Order Created'); // Should still show SO creation
    }

    /** @test */
    public function timeline_detail_view_displays_volume_and_amounts_correctly()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        // Create delivery order with allowance
        $deliveryOrder = DeliveryOrder::factory()->create(['id_transaksi' => $salesOrder->id]);
        $allowance = UangJalan::factory()->create([
            'id_do' => $deliveryOrder->id,
            'nominal' => 750000,
        ]);

        // Create delivery progress with volume
        PengirimanDriver::factory()->create([
            'id_do' => $deliveryOrder->id,
            'volume_terkirim' => 2500.50,
        ]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);
        $response->assertSee('IDR 750,000'); // Formatted allowance amount
        $response->assertSee('2,500.50 L'); // Formatted volume
    }

    /** @test */
    public function timeline_detail_view_responsive_design_elements()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);

        // Check for responsive grid classes
        $response->assertSee('grid-cols-1');
        $response->assertSee('md:grid-cols-4');

        // Check for responsive spacing
        $response->assertSee('space-y-6');

        // Check for dark mode classes
        $response->assertSee('dark:bg-gray-800');
        $response->assertSee('dark:text-white');
    }

    /** @test */
    public function timeline_detail_view_accessibility_features()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);

        // Check for ARIA attributes
        $response->assertSee('role="list"');
        $response->assertSee('aria-hidden="true"');

        // Check for semantic HTML structure
        $response->assertSee('<ul');
        $response->assertSee('<li>');
    }

    /** @test */
    public function timeline_detail_view_performance_with_many_events()
    {
        $this->actingAs($this->user);

        $salesOrder = $this->createSalesOrder();

        // Create multiple delivery orders with full timeline
        for ($i = 0; $i < 10; $i++) {
            $deliveryOrder = DeliveryOrder::factory()->create([
                'id_transaksi' => $salesOrder->id,
                'waktu_muat' => now()->subDays($i)->subHours(2),
                'waktu_selesai_muat' => now()->subDays($i)->subHours(1),
            ]);

            UangJalan::factory()->create(['id_do' => $deliveryOrder->id]);
            PengirimanDriver::factory()->create(['id_do' => $deliveryOrder->id]);
        }

        $startTime = microtime(true);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        // Should render within reasonable time
        $this->assertLessThan(3.0, $executionTime);
    }

    /** @test */
    public function timeline_detail_view_handles_special_characters_in_data()
    {
        $this->actingAs($this->user);

        $customer = Pelanggan::factory()->create([
            'nama' => 'Test & Company <script>alert("xss")</script>',
        ]);

        $salesOrder = TransaksiPenjualan::factory()->create([
            'id_pelanggan' => $customer->id,
            'id_tbbm' => $this->tbbm->id,
            'kode' => 'SO-SPECIAL-<>&"',
        ]);

        PenjualanDetail::factory()->create([
            'id_transaksi_penjualan' => $salesOrder->id,
            'id_item' => $this->fuelItem->id,
        ]);

        $response = $this->get("/admin/sales-order-timeline-detail?record={$salesOrder->id}");

        $response->assertStatus(200);

        // Should escape special characters
        $response->assertSee('Test &amp; Company');
        $response->assertSee('SO-SPECIAL-&lt;&gt;&amp;&quot;');
        $response->assertDontSee('<script>');
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
