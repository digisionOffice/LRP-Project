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
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Tbbm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class FuelDeliveryDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
        ]);
    }

    /** @test */
    public function dashboard_page_loads_successfully()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/fuel-delivery-dashboard');

        $response->assertStatus(200);
        $response->assertSee('Fuel Delivery Dashboard');
    }

    /** @test */
    public function sales_tab_displays_sales_orders()
    {
        // Create test data
        $customer = Pelanggan::create([
            'kode' => 'CUST-001',
            'type' => 'corporate',
            'nama' => 'Test Customer',
            'pic_nama' => 'Test PIC',
            'pic_phone' => '081234567890',
            'alamat' => 'Test Address',
            'created_by' => $this->user->id,
        ]);

        $salesOrder = TransaksiPenjualan::create([
            'kode' => 'SO-001',
            'tipe' => 'dagang',
            'tanggal' => now(),
            'id_pelanggan' => $customer->id,
            'alamat' => 'Delivery Address',
            'nomor_po' => 'PO-001',
            'top_pembayaran' => 30,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/fuel-delivery-dashboard?tab=sales');

        $response->assertStatus(200);
        $response->assertSee('SO-001');
        $response->assertSee('Test Customer');
    }

    /** @test */
    public function operations_tab_displays_delivery_orders()
    {
        // Create test data
        $customer = Pelanggan::create([
            'kode' => 'CUST-001',
            'type' => 'corporate',
            'nama' => 'Test Customer',
            'pic_nama' => 'Test PIC',
            'pic_phone' => '081234567890',
            'alamat' => 'Test Address',
            'created_by' => $this->user->id,
        ]);

        $salesOrder = TransaksiPenjualan::create([
            'kode' => 'SO-001',
            'tipe' => 'dagang',
            'tanggal' => now(),
            'id_pelanggan' => $customer->id,
            'alamat' => 'Delivery Address',
            'created_by' => $this->user->id,
        ]);

        $deliveryOrder = DeliveryOrder::create([
            'kode' => 'DO-001',
            'id_transaksi' => $salesOrder->id,
            'tanggal_delivery' => now()->addDay(),
            'status_muat' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/fuel-delivery-dashboard?tab=operations');

        $response->assertStatus(200);
        $response->assertSee('DO-001');
        $response->assertSee('Load Order Issued');
    }

    /** @test */
    public function administration_tab_displays_administrative_data()
    {
        // Create test data
        $customer = Pelanggan::create([
            'kode' => 'CUST-001',
            'type' => 'corporate',
            'nama' => 'Test Customer',
            'pic_nama' => 'Test PIC',
            'pic_phone' => '081234567890',
            'alamat' => 'Test Address',
            'created_by' => $this->user->id,
        ]);

        $salesOrder = TransaksiPenjualan::create([
            'kode' => 'SO-001',
            'tipe' => 'dagang',
            'tanggal' => now(),
            'id_pelanggan' => $customer->id,
            'alamat' => 'Delivery Address',
            'created_by' => $this->user->id,
        ]);

        $deliveryOrder = DeliveryOrder::create([
            'kode' => 'DO-001',
            'id_transaksi' => $salesOrder->id,
            'no_segel' => 'SEAL-001',
            'do_signatory_name' => 'Test Signatory',
            'do_print_status' => true,
            'driver_allowance_amount' => 250000,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/fuel-delivery-dashboard?tab=administration');

        $response->assertStatus(200);
        $response->assertSee('SEAL-001');
        $response->assertSee('Test Signatory');
    }

    /** @test */
    public function driver_tab_displays_driver_activities()
    {
        // Create test data
        $customer = Pelanggan::create([
            'kode' => 'CUST-001',
            'type' => 'corporate',
            'nama' => 'Test Customer',
            'pic_nama' => 'Test PIC',
            'pic_phone' => '081234567890',
            'alamat' => 'Test Address',
            'created_by' => $this->user->id,
        ]);

        $salesOrder = TransaksiPenjualan::create([
            'kode' => 'SO-001',
            'tipe' => 'dagang',
            'tanggal' => now(),
            'id_pelanggan' => $customer->id,
            'alamat' => 'Delivery Address',
            'created_by' => $this->user->id,
        ]);

        $deliveryOrder = DeliveryOrder::create([
            'kode' => 'DO-001',
            'id_transaksi' => $salesOrder->id,
            'created_by' => $this->user->id,
        ]);

        $driverDelivery = PengirimanDriver::create([
            'id_do' => $deliveryOrder->id,
            'totalisator_awal' => 10000,
            'totalisator_tiba' => 15000,
            'waktu_mulai' => now(),
            'waktu_tiba' => now()->addHours(2),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/fuel-delivery-dashboard?tab=driver');

        $response->assertStatus(200);
        $response->assertSee('SO-001');
    }

    /** @test */
    public function finance_tab_displays_financial_data()
    {
        // Create test data
        $customer = Pelanggan::create([
            'kode' => 'CUST-001',
            'type' => 'corporate',
            'nama' => 'Test Customer',
            'pic_nama' => 'Test PIC',
            'pic_phone' => '081234567890',
            'alamat' => 'Test Address',
            'created_by' => $this->user->id,
        ]);

        $salesOrder = TransaksiPenjualan::create([
            'kode' => 'SO-001',
            'tipe' => 'dagang',
            'tanggal' => now(),
            'id_pelanggan' => $customer->id,
            'alamat' => 'Delivery Address',
            'created_by' => $this->user->id,
        ]);

        $deliveryOrder = DeliveryOrder::create([
            'kode' => 'DO-001',
            'id_transaksi' => $salesOrder->id,
            'invoice_number' => 'INV-001',
            'tax_invoice_number' => 'TAX-001',
            'payment_status' => 'pending',
            'invoice_delivery_status' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/admin/fuel-delivery-dashboard?tab=finance');

        $response->assertStatus(200);
        $response->assertSee('INV-001');
        $response->assertSee('TAX-001');
        $response->assertSee('Pending');
    }

    /** @test */
    public function dashboard_summary_cards_display_correct_counts()
    {
        // Create test data
        $customer = Pelanggan::create([
            'kode' => 'CUST-001',
            'type' => 'corporate',
            'nama' => 'Test Customer',
            'pic_nama' => 'Test PIC',
            'pic_phone' => '081234567890',
            'alamat' => 'Test Address',
            'created_by' => $this->user->id,
        ]);

        // Create 3 sales orders
        for ($i = 1; $i <= 3; $i++) {
            $salesOrder = TransaksiPenjualan::create([
                'kode' => "SO-00{$i}",
                'tipe' => 'dagang',
                'tanggal' => now(),
                'id_pelanggan' => $customer->id,
                'alamat' => 'Delivery Address',
                'created_by' => $this->user->id,
            ]);

            DeliveryOrder::create([
                'kode' => "DO-00{$i}",
                'id_transaksi' => $salesOrder->id,
                'status_muat' => $i <= 2 ? 'pending' : 'selesai',
                'payment_status' => $i <= 2 ? 'pending' : 'paid',
                'created_by' => $this->user->id,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get('/admin/fuel-delivery-dashboard');

        $response->assertStatus(200);
        $response->assertSee('3'); // Total Sales Orders
        $response->assertSee('2'); // Active Deliveries (pending status)
        $response->assertSee('1'); // Completed Deliveries (selesai status)
        $response->assertSee('2'); // Pending Payments
    }
}
